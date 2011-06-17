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

        $request = $this->get('request');

        if( $request->getMethod() == 'POST' && $request->get('create')) {
            $page = $this->admin->getParent()->getSubject();
            $snapshotManager = $this->get('sonata.page.manager.snapshot');

            $snapshot = $snapshotManager->save($snapshotManager->create($page));

            return $this->redirect( $this->admin->generateUrl('edit', array('id' => $snapshot->getId())));
        }

        return $this->render('SonataPageBundle:SnapshotAdmin:create.html.twig', array(
            'action'        => 'create',
            'admin'         => $this->admin,
            'base_template' => $this->getBaseTemplate(),
        ));
    }

    public function batchActionToggleEnabled(array $idx)
    {
        if (!$this->get('security.context')->isGranted('ROLE_SONATA_PAGE_ADMIN_BLOCK_EDIT')) {
            throw new AccessDeniedException();
        }

        $snapshotManager = $this->get('sonata.page.manager.snapshot');
        foreach($idx as $id) {
            $snapshot = $snapshotManager->findOneBy(array('id' => $id));

            if ($snapshot) {
                $snapshot->setEnabled(!$snapshot->getEnabled());
                $snapshotManager->save($snapshot);
            }
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}