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
            $cms = $this->get('sonata.page.cms.page');
            $page = $cms->getPageBySlug($pathInfo);

            if (!$page) {
                throw new NotFoundHttpException('The current page does not exist!');
            }
        } else {
            $manager = $this->get('sonata.page.manager.snapshot');

            $snapshot = $manager->findOneBy(array(
                'slug' => $pathInfo,
                'enabled' => true
            ));

            if (!$snapshot) {
                throw new NotFoundHttpException('The current snapshot does not exist!');
            }

            $page = $manager->load($snapshot);

            $cms = $this->get('sonata.page.cms.snapshot');
        }

        $cms->setCurrentPage($page);

        return $cms->renderPage($page);
    }
}