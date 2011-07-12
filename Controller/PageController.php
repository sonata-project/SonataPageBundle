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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller
{
    public function catchAllAction()
    {
        $pathInfo = $this->get('request')->getPathInfo();

        // always render the last page version for the admin
        if ($this->get('security.context')->isGranted('ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT')) {
            $cms  = $this->get('sonata.page.cms.page');
            $page = $cms->getPageByUrl($pathInfo);

            if (!$page) {
                $page = $cms->getPageByRouteName('global');
                $page->setDecorate(false);
                $cms->setCurrentPage($page);

                return $this->render('SonataPageBundle:Page:create.html.twig', array(
                    'pathInfo'   => $pathInfo,
                    'page'       => $page,
                    'page_admin' => $this->get('sonata.page.admin.page'),
                    'manager'    => $cms,
                    'creatable'  => $cms->isRouteNameDecorable($this->get('request')->get('_route')) && $cms->isRouteUriDecorable($pathInfo)
                ));

            }
        } else {
            $manager  = $this->get('sonata.page.manager.snapshot');

            $snapshot = $manager->findEnableSnapshot(array(
                'url'     => $pathInfo,
            ));

            if (!$snapshot) {
                throw new NotFoundHttpException('The current snapshot does not exist!');
            }

            $page = $manager->load($snapshot);
            $cms  = $this->get('sonata.page.cms.snapshot');
        }

        $cms->setCurrentPage($page);

        return $cms->renderPage($page);
    }
}