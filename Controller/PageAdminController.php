<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\Sonata\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\TextField;
use Symfony\Component\Form\CheckboxField;
use Symfony\Component\Form\TextareaField;
use Symfony\Component\Form\DateTimeField;
use Symfony\Component\Form\HiddenField;

class PageAdminController extends Controller
{

    public function editAction($id, $form = null)
    {
        // todo : add security layer

        $em = $this->get('doctrine.orm.default_entity_manager');

        $page = is_object($id) ? $id : $em->find('Application\Sonata\PageBundle\Entity\Page', $id);

        if(!$page) {

            throw new NotFoundHttpException(sprintf('Page id %d not found', $id));
        }

        $this->get('session')->start();
        
        return $this->render('SonataPageBundle:Page:edit.twig.html', array(
            'page' => $page,
            'form' => $form ?: $this->getForm($page)
        ));
    }

    public function getForm($page)
    {
        $form = new Form('page', $page, $this->get('validator'));

        $form->add(new CheckboxField('enabled'));
        $form->add(new CheckboxField('decorate'));

        $form->add(new TextField('name'));
        
        $form->add(new TextareaField('meta_keyword'));
        $form->add(new TextareaField('meta_description'));
        $form->add(new TextareaField('javascript'));
        $form->add(new TextareaField('stylesheet'));

        $form->add(new DateTimeField('publication_date_start'));
        $form->add(new DateTimeField('publication_date_end'));

        return $form;
    }

    public function updateAction()
    {
        // todo : add security layer
        
        if($this->get('request')->getMethod() != 'POST') {
            throw new \RuntimeException('invalid request type');
        }

        $em = $this->get('doctrine.orm.default_entity_manager');
        $id = $this->get('request')->get('id');

        $page = $em->find('Application\Sonata\PageBundle\Entity\Page', $id);
        $page = $page ?: new \Application\Sonata\PageBundle\Entity\Page;

        $form = $this->getForm($page);
        $form->setValidationGroups($page->isHybrid() ? 'action_route' : 'cms_route');

        $form->bind($this->get('request')->get('page'));

        if($form->isValid()) {

            $em = $this->get('doctrine.orm.default_entity_manager');
            $em->persist($form->getData());
            $em->flush();

            return $this->redirect($this->generateUrl('page_edit', array('id' => $page->getId())));
        }

        return $this->forward('SonataPageBundle:PageAdmin:edit', array(
            'id' => $page,
            'form' => $form
        ));
    }
}