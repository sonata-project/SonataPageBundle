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

class CacheController extends Controller
{

    public function esiAction()
    {
        $request = $this->get('request');

        $manager = $this->get('sonata.page.manager');
        $page    = $manager->getPageById($request->get('page_id'));
        $block   = $manager->getBlock($request->get('block_id'));

        return $manager->renderBlock($block, $page, false);
    }

    public function jsAction()
    {
        $request = $this->get('request');

        $manager = $this->get('sonata.page.manager');
        $page    = $manager->getPageById($request->get('page_id'));
        $block   = $manager->getBlock($request->get('block_id'));

        return $manager->renderBlock($block, $page, false);
    }
}