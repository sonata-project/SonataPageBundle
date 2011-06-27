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

class PageAdminController extends Controller
{
    public function batchActionSnapshot($query)
    {
        if (!$this->get('security.context')->isGranted('ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT')) {
            throw new AccessDeniedException();
        }

        $snapshotManager = $this->get('sonata.page.manager.snapshot');
        foreach ($query->execute() as $page) {
            $page = $this->get('sonata.page.cms.page')->getPageById($page->getId());

            if ($page) {
                $snapshot = $snapshotManager->create($page);
                $snapshotManager->save($snapshot);

                $snapshots[] = $snapshot;
            }
        }

        $snapshotManager->enableSnapshots($snapshots);

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }

    public function snapshotsAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT')) {
            throw new AccessDeniedException();
        }

        if ($this->get('request')->getMethod() == "POST") {
            $snapshotManager = $this->get('sonata.page.manager.snapshot');

            $pages = $this->get('sonata.page.manager.page')->findBy(array());
            $snapshots = array();
            foreach ($pages as $page) {
                $snapshot = $snapshotManager->create($page);
                $snapshotManager->save($snapshot);

                $snapshots[] = $snapshot;
            }

            $snapshotManager->enableSnapshots($snapshots);

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        return $this->render('SonataPageBundle:PageAdmin:create_snapshots.html.twig', array(
            'action'            => 'snapshots',
            'admin'             => $this->admin,
            'base_template'     => $this->getBaseTemplate(),
        ));
    }
}