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

        $snapshots = array();
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

    public function createAction()
    {
        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        if ($this->getRequest()->getMethod() == 'GET' && !$this->getRequest()->get('siteId')) {
            $sites = $this->get('sonata.page.manager.site')->findBy();

            if (count($sites) == 1) {
                return $this->redirect($this->admin->generateUrl('create', array('siteId' => $sites[0]->getId())));
            }

            try {
                $current = $this->get('sonata.page.site.selector')->retrieve();
            } catch(\RuntimeException $e) {
                $current = false;
            }

            return $this->render('SonataPageBundle:PageAdmin:select_site.html.twig', array(
                'sites'  => $sites,
                'current' => $current,
            ));
        }

        return parent::createAction();
    }
}