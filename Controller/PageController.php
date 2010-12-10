<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\PageBundle\Controller;

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

            return $this->render('PageBundle:Block:block_no_page_available.twig');
        }

        if($parent_container) {
            // parent container is set, nothing to find, don't need to loop across the
            // name to fins the correct container (main template level)
            $container = $parent_container;
        } else {
            // todo : put this code in the Manager
          
            // find the related container
            $container = false;

            // first level block are always container
            if($page->getBlocks()) {
                foreach($page->getBlocks() as $block) {
                    if($block->getSetting('name') == $name) {

                        $container = $block;
                        break;
                    }
                }
            }

            // no container, create it!
            if(!$container) {
                $container = new \Application\PageBundle\Entity\Block;
                $container->setEnabled(true);
                $container->setCreatedAt(new \DateTime);
                $container->setUpdatedAt(new \DateTime);
                $container->setType('core.container');
                $container->setPage($page);
                $container->setSettings(array('name' => $name));
                $container->setPosition(1);

                if($parent_container) {
                    $container->setParent($parent_container);
                }

                $em = $this->container->get('doctrine.orm.default_entity_manager');
                $em->persist($container);
                $em->flush();
            }
        }

        return $this->render('PageBundle:Block:block_container.twig', array(
            'container' => $container,
            'manager'   => $this->get('page.manager'),
            'page'      => $page,
        ));
    }
}