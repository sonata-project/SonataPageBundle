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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Snapshot Admin Controller
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SnapshotAdminController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function createAction()
    {
        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $class = $this->get('sonata.page.manager.snapshot')->getClass();

        $pageManager = $this->get('sonata.page.manager.page');

        $snapshot = new $class;

        if ($this->getRequest()->getMethod() == 'GET' && $this->getRequest()->get('pageId')) {
            $page = $pageManager->findOne(array('id' => $this->getRequest()->get('pageId')));
        } elseif ($this->admin->isChild()) {
            $page = $this->admin->getParent()->getSubject();
        } else {
            $page = null; // no page selected ...
        }

        $snapshot->setPage($page);

        $form = $this->createForm('sonata_page_create_snapshot', $snapshot);

        if ($this->getRequest()->getMethod() == 'POST') {

            $form->bind($this->getRequest());

            if ($form->isValid()) {
                $snapshotManager = $this->get('sonata.page.manager.snapshot');
                $transformer = $this->get('sonata.page.transformer');

                $page = $form->getData()->getPage();
                $page->setEdited(false);

                $snapshot = $transformer->create($page);

                $this->admin->create($snapshot);

                $pageManager->save($page);

                $snapshotManager->enableSnapshots(array($snapshot));
            }

            return $this->redirect($this->admin->generateUrl('edit', array(
                'id' => $snapshot->getId()
            )));
        }

        return $this->render('SonataPageBundle:SnapshotAdmin:create.html.twig', array(
            'action'  => 'create',
            'form'    => $form->createView()
        ));
    }

    /**
     * @param mixed $query
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function batchActionToggleEnabled($query)
    {
        if (false === $this->admin->isGranted('EDIT')) {
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
