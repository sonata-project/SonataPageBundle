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
use Sonata\PageBundle\Form\Type\CreateSnapshotType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Snapshot Admin Controller.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SnapshotAdminController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request = null)
    {
        $this->admin->checkAccess('create');

        $class = $this->get('sonata.page.manager.snapshot')->getClass();

        $pageManager = $this->get('sonata.page.manager.page');

        $snapshot = new $class();

        if ('GET' == $request->getMethod() && $request->get('pageId')) {
            $page = $pageManager->find($request->get('pageId'));
        } elseif ($this->admin->isChild()) {
            $page = $this->admin->getParent()->getSubject();
        } else {
            $page = null; // no page selected ...
        }

        $snapshot->setPage($page);

        $form = $this->createForm(CreateSnapshotType::class, $snapshot);

        if ('POST' == $request->getMethod()) {
            $form->submit($request->request->get($form->getName()));

            if ($form->isValid()) {
                $snapshotManager = $this->get('sonata.page.manager.snapshot');
                $transformer = $this->get('sonata.page.transformer');

                $page = $form->getData()->getPage();
                $page->setEdited(false);

                $snapshot = $transformer->create($page);

                $this->admin->create($snapshot);

                $pageManager->save($page);

                $snapshotManager->enableSnapshots([$snapshot]);
            }

            return $this->redirect($this->admin->generateUrl('edit', [
                'id' => $snapshot->getId(),
            ]));
        }

        return $this->render('SonataPageBundle:SnapshotAdmin:create.html.twig', [
            'action' => 'create',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param mixed $query
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException
     */
    public function batchActionToggleEnabled($query)
    {
        $this->admin->checkAccess('batchToggleEnabled');

        $snapshotManager = $this->get('sonata.page.manager.snapshot');
        foreach ($query->getQuery()->iterate() as $snapshot) {
            $snapshot[0]->setEnabled(!$snapshot[0]->getEnabled());
            $snapshotManager->save($snapshot[0]);
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
