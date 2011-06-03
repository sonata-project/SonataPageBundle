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

        $manager = $this->get('sonata.page.manager');
        $page = $manager->getPageBySlug($pathInfo);

        if (!$page) {
            throw new NotFoundHttpException('The current page does not exist!');
        }

        $manager->setCurrentPage($page);

        return new Response($manager->renderPage($page));
    }

    public function renderContainerAction($name, $page = null, $parentContainer = null)
    {
        $manager = $this->get('sonata.page.manager');
        $page = $manager->getPage($page);

        return $manager->renderContainer($name, $page, $parentContainer);
    }
}