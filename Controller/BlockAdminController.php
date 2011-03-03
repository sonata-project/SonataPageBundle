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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

class BlockAdminController extends Controller
{
    public function savePositionAction()
    {
        // todo : add security check
        $params = $this->get('request')->get('disposition');

        $result = $this->get('page.manager')->savePosition($params);

        return $this->renderJson(array('result' => $result ? 'ok' : 'ko'));
    }

    public function editAction($id, $form = null)
    {

        $this->get('session')->start();
        $manager  = $this->get('page.manager');

        // clean the id
        if(!is_object($id)) {
            $block = $manager->getBlock($id);

            if(!$block) {
                throw new NotFoundHttpException(sprintf('block not found (id: %d)', $id));
            }
        } else {
            $block = $id;
        }

        $service = $manager->getBlockService($block);

        return $this->render($service->getEditTemplate(), array(
            'object'    => $block,
            'admin'     => $this->admin,
            'form'      => $form ?: $this->getForm($block),
            'service'   => $service,
            'manager'   => $manager,
            'base_template'  => $this->getBaseTemplate(),
            'side_menu'      => $this->getSideMenu('edit'),
            'breadcrumbs'    => $this->getBreadcrumbs('edit'),
        ));
    }

    public function getForm($block)
    {
        $form = new \Symfony\Component\Form\Form('block', array(
            'data' => $block,
            'validator' => $this->get('validator'),
            'validation_groups' => array($block->getType())
        ));

        $this->get('page.manager')->defineBlockForm($form);

        return $form;
    }

    public function updateAction()
    {

        $this->get('session')->start();

        // clean the id
        $id    = $this->get('request')->get('id');

        $block = $this->get('page.manager')->getBlock($id);

        if(!$block) {
            throw new NotFoundHttpException(sprintf('block not found (id: %d)', $id));
        }

        $form = $this->getForm($block);
        $form->bind($this->get('request')->get('block'));

        if($form->isValid()){
            $em = $this->get('doctrine.orm.default_entity_manager');
            $em->persist($block);
            $em->flush();

            return new RedirectResponse($this->admin->generateUrl('edit', array('id' => $block->getId())));
        }

        return $this->forward('SonataPageBundle:BlockAdmin:edit', array(
            'id'    => $block,
            'form'  => $form
        ));
    }

}