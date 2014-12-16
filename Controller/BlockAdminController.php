<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\PageBundle\Admin\SharedBlockAdmin;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Block Admin Controller
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BlockAdminController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function savePositionAction()
    {
        if (!$this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        try {
            $params = $this->get('request')->get('disposition');

            if (!is_array($params)) {
                throw new HttpException(400, 'wrong parameters');
            }

            $result = $this->get('sonata.page.block_interactor')->saveBlocksPosition($params, false);

            $status = 200;

            $pageAdmin = $this->get('sonata.page.admin.page');
            $pageAdmin->setRequest($this->get('request'));
            $pageAdmin->update($pageAdmin->getSubject());
        } catch (HttpException $e) {
            $status = $e->getStatusCode();
            $result = array(
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'code'      => $e->getCode()
            );

        } catch (\Exception $e) {
            $status = 500;
            $result = array(
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'code'      => $e->getCode()
            );
        }

        $result = ($result === true) ? 'ok' : $result;

        return $this->renderJson(array('result' => $result), $status);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        if (!$this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $sharedBlockAdminClass = $this->container->getParameter('sonata.page.admin.shared_block.class');
        if (!$this->admin->getParent() && get_class($this->admin) !== $sharedBlockAdminClass) {
            
            throw new PageNotFoundException('You cannot create a block without a page');
        }

        $parameters = $this->admin->getPersistentParameters();

        if (!$parameters['type']) {
            return $this->render('SonataPageBundle:BlockAdmin:select_type.html.twig', array(
                'services'      => $this->get('sonata.block.manager')->getServicesByContext('sonata_page_bundle'),
                'base_template' => $this->getBaseTemplate(),
                'admin'         => $this->admin,
                'action'        => 'create'
            ));
        }

        return parent::createAction();
    }

    public function switchParentAction()
    {
        if (!$this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $blockId  = $this->get('request')->get('block_id');
        $parentId = $this->get('request')->get('parent_id');
        if ($blockId === null or $parentId === null) {
            throw new HttpException(400, 'wrong parameters');
        }

        $block = $this->admin->getObject($blockId);
        if (!$block) {
            throw new PageNotFoundException(sprintf('Unable to find block with id %d', $blockId));
        }

        $parent = $this->admin->getObject($parentId);
        if (!$block) {
            throw new PageNotFoundException(sprintf('Unable to find parent block with id %d', $parentId));
        }

        $parent->addChildren($block);
        $this->admin->update($parent);

        return $this->renderJson(array('result' => 'ok'));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Sonata\PageBundle\Exception\PageNotFoundException
     */
    public function composePreviewAction()
    {
        if (!$this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $blockId = $this->get('request')->get('block_id');

        /** @var \Sonata\PageBundle\Entity\BaseBlock $block */
        $block = $this->admin->getObject($blockId);
        if (!$block) {
            throw new PageNotFoundException(sprintf('Unable to find block with id %d', $blockId));
        }

        $container = $block->getParent();
        if (!$container) {
            throw new PageNotFoundException('No parent found, unable to preview an orphan block');
        }

        $blockServices = $this->get('sonata.block.manager')->getServicesByContext('sonata_page_bundle', false);

        return $this->render('SonataPageBundle:BlockAdmin:compose_preview.html.twig', array(
            'container'     => $container,
            'child'         => $block,
            'blockServices' => $blockServices,
        ));
    }
}
