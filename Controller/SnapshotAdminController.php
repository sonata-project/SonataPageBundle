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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SnapshotAdminController extends Controller
{
    public function createAction()
    {
        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $class = $this->get('sonata.page.manager.snapshot')->getClass();
        $snapshot = new $class;

        if ($this->getRequest()->getMethod() == 'GET' && $this->getRequest()->get('pageId')) {
            $page = $this->get('sonata.page.manager.page')->findOne(array('id' => $this->getRequest()->get('pageId')));
        } elseif ($this->admin->isChild()) {
            $page = $this->admin->getParent()->getSubject();
        } else {
            $page = null; // no page selected ...
        }

        $snapshot->setPage($page);

        $form = $this->createForm('sonata_page_create_snapshot', $snapshot);

        if ( $this->getRequest()->getMethod() == 'POST') {

            $form->bindRequest($this->getRequest());

            if ($form->isValid()) {
                $snapshotManager = $this->get('sonata.page.manager.snapshot');

                $snapshot = $snapshotManager->create($form->getData()->getPage());
                $snapshotManager->save($snapshot);
                $snapshotManager->enableSnapshots($snapshot);
            }

            return $this->redirect( $this->admin->generateUrl('edit', array('id' => $snapshot->getId())));
        }

        return $this->render('SonataPageBundle:SnapshotAdmin:create.html.twig', array(
            'action'  => 'create',
            'form'    => $form->createView()
        ));
    }

    public function batchActionToggleEnabled($query)
    {
        if (!$this->get('security.context')->isGranted('ROLE_SONATA_PAGE_ADMIN_BLOCK_EDIT')) {
            throw new AccessDeniedException();
        }

        $snapshotManager = $this->get('sonata.page.manager.snapshot');
        foreach ($query->getQuery()->iterate() as $snapshot) {
            $snapshot[0]->setEnabled(!$snapshot[0]->getEnabled());
            $snapshotManager->save($snapshot[0]);
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}