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
    public function batchActionSnapshot(array $idx)
    {
        if (!$this->get('security.context')->isGranted('ROLE_SONATA_PAGE_ADMIN_BLOCK_EDIT')) {
            throw new AccessDeniedException();
        }

        $snapshotManager = $this->get('sonata.page.manager.snapshot');
        foreach($idx as $id) {
            $page = $this->get('sonata.page.cms.page')->getPageById($id);

            if ($page) {
                $snapshotManager->save($snapshotManager->create($page));
            }
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}