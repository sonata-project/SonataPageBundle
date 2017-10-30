<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\PageBundle\Entity\BaseBlock;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Block Admin Controller.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BlockAdminController extends Controller
{
    /**
     * @param Request|null $request
     *
     * @return Response
     *
     * @throws AccessDeniedException
     */
    public function savePositionAction(Request $request = null)
    {
        $this->admin->checkAccess('savePosition');

        try {
            $params = $request->get('disposition');

            if (!is_array($params)) {
                throw new HttpException(400, 'wrong parameters');
            }

            $result = $this->get('sonata.page.block_interactor')->saveBlocksPosition($params, false);

            $status = 200;

            $pageAdmin = $this->get('sonata.page.admin.page');
            $pageAdmin->setRequest($request);
            $pageAdmin->update($pageAdmin->getSubject());
        } catch (HttpException $e) {
            $status = $e->getStatusCode();
            $result = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        } catch (\Exception $e) {
            $status = 500;
            $result = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }

        $result = (true === $result) ? 'ok' : $result;

        return $this->renderJson(['result' => $result], $status);
    }

    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request = null)
    {
        $this->admin->checkAccess('create');

        $sharedBlockAdminClass = $this->container->getParameter('sonata.page.admin.shared_block.class');
        if (!$this->admin->getParent() && get_class($this->admin) !== $sharedBlockAdminClass) {
            throw new PageNotFoundException('You cannot create a block without a page');
        }

        $parameters = $this->admin->getPersistentParameters();

        if (!$parameters['type']) {
            return $this->render('SonataPageBundle:BlockAdmin:select_type.html.twig', [
                'services' => $this->get('sonata.block.manager')->getServicesByContext('sonata_page_bundle'),
                'base_template' => $this->getBaseTemplate(),
                'admin' => $this->admin,
                'action' => 'create',
            ]);
        }

        return parent::createAction();
    }

    /**
     * @param Request|null $request
     *
     * @return Response
     */
    public function switchParentAction(Request $request = null)
    {
        $this->admin->checkAccess('switchParent');

        $blockId = $request->get('block_id');
        $parentId = $request->get('parent_id');
        if (null === $blockId or null === $parentId) {
            throw new HttpException(400, 'wrong parameters');
        }

        $block = $this->admin->getObject($blockId);
        if (!$block) {
            throw new PageNotFoundException(sprintf('Unable to find block with id %d', $blockId));
        }

        $parent = $this->admin->getObject($parentId);
        if (!$parent) {
            throw new PageNotFoundException(sprintf('Unable to find parent block with id %d', $parentId));
        }

        $parent->addChildren($block);
        $this->admin->update($parent);

        return $this->renderJson(['result' => 'ok']);
    }

    /**
     * @param Request|null $request
     *
     * @return Response
     *
     * @throws AccessDeniedException
     * @throws PageNotFoundException
     */
    public function composePreviewAction(Request $request = null)
    {
        $this->admin->checkAccess('composePreview');

        $blockId = $request->get('block_id');

        /** @var BaseBlock $block */
        $block = $this->admin->getObject($blockId);
        if (!$block) {
            throw new PageNotFoundException(sprintf('Unable to find block with id %d', $blockId));
        }

        $container = $block->getParent();
        if (!$container) {
            throw new PageNotFoundException('No parent found, unable to preview an orphan block');
        }

        $blockServices = $this->get('sonata.block.manager')->getServicesByContext('sonata_page_bundle', false);

        return $this->render('SonataPageBundle:BlockAdmin:compose_preview.html.twig', [
            'container' => $container,
            'child' => $block,
            'blockServices' => $blockServices,
        ]);
    }
}
