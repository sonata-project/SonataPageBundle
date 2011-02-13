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

class PageController extends Controller
{

    public function renderContainerAction($name, $page = null, $parent_container = null)
    {

        if(is_string($page)) { // page is a slug, load the related page
            $page = $this->get('page.manager')->getPageByRouteName($page);
        } else if(!$page)    { // get the current page
            $page = $this->get('page.manager')->getCurrentPage();
        } else {
            // the page is provided, here in a nested container
        }

        if(!$page) {

            return $this->render('SonataPageBundle:Block:block_no_page_available.html.twig');
        }

        $container = $this->get('page.manager')->findContainer($name, $page, $parent_container);


        return $this->render('SonataPageBundle:Block:block_container.html.twig', array(
            'container' => $container,
            'manager'   => $this->get('page.manager'),
            'page'      => $page,
        ));
    }
}