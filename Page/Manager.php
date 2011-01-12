<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bundle\Sonata\PageBundle\Page;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Application\Sonata\PageBundle\Entity\Page;

/**
 * The Manager class is in charge of retrieving the correct page (cms page or action page)
 *
 * An action page is linked to a symfony action and a cms page is a standalone page.
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class Manager extends ContainerAware
{
    protected $route_pages = array();

    protected $current_page = null;

    protected $blocks = array();

    protected $options = array();

    /**
     * filter the `core.response` event to decorated the action
     *
     * @param  $event
     * @param  $response
     * @return
     */
    public function filterReponse($event, $response)
    {
        $kernel       = $event->getSubject();
        $request_type = $event->get('request_type');

        
        if($this->isDecorable($request_type, $response)) {

            $page = $this->getCurrentPage();

            if ($page && $page->getDecorate()) {
                $template = 'Sonata/PageBundle::layout.twig';
                if($this->getCurrentPage()) {
                    $template = $this->getCurrentPage()->getTemplate()->getPath();
                }

                $response->setContent(
                    $this->container->get('templating')->render(
                        $template,
                        array(
                            'content'   => $response->getContent(),
                            'page'      => $page
                        )
                    )
                );
            }
        }

        $event->setReturnValue($response);
        
        return $response;
    }

    public function isDecorable($request_type, $response)
    {

        if($request_type != HttpKernelInterface::MASTER_REQUEST) {

            return false;
        }

        if(($response->headers->get('Content-Type') ?: 'text/html') != 'text/html') {

            return false;
        }

        if($response->getStatusCode() != 200) {

            return false;
        }

        if($this->container->get('kernel')->getRequest()->headers->get('x-requested-with') == 'XMLHttpRequest') {

            return false;
        }

        $route_name = $this->container->get('request')->get('_route');
        foreach($this->getOption('ignore_routes', array()) as $route) {

            if($route_name == $route) {
                return false;
            }
        }

        foreach($this->getOption('ignore_route_patterns', array()) as $route_pattern) {
            if(preg_match($route_pattern, $route_name)) {

                return false;
            }
        }

        $uri = $this->container->get('request')->getRequestUri();
        foreach($this->getOption('ignore_uri_patterns', array()) as $uri_pattern) {
            if(preg_match($uri_pattern, $uri)) {

                return false;
            }
        }

        return true;

    }

    /**
     * render a specialize block
     *
     * @param  $block
     * @return string | Response
     */
    public function renderBlock($block, $page)
    {
        try {
            $service = $this->getBlockService($block);

            if(!$service) {
                
                return '';
            }
            return $service->execute($block, $page);
        } catch (\Exception $e) {
            if($this->container->getParameter('kernel.debug')) {

                throw $e;
            }
            $this->container->get('logger')->crit(sprintf('[cms::renderBlock] block.id=%d - error while rendering block - %s', $block->getId(), $e->getMessage()));
        }

        return '';
    }

    public function findContainer($name, $page, $parent_container = null)
    {

        $container = false;
        
        if($parent_container) {
            // parent container is set, nothing to find, don't need to loop across the
            // name to find the correct container (main template level)
            $container = $parent_container;
        }

        // first level blocks are containers
        if(!$container && $page->getBlocks()) {
            foreach($page->getBlocks() as $block) {
                if($block->getSetting('name') == $name) {

                    $container = $block;
                    break;
                }
            }
        }

        if(!$container) {
          
            // no container, create it!
            $container = new \Application\Sonata\PageBundle\Entity\Block;
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

        return $container;
    }

    
    /**
     *
     * return the block service linked to the link
     * 
     * @param  $block
     * @return bool
     */
    public function getBlockService($block)
    {
        $id = sprintf('page.block.%s', $block->getType());

        try {
            return $this->container->get($id);
        } catch (\Exception $e) {
            if($this->container->getParameter('kernel.debug')) {
                throw $e;
            }
            $this->container->get('logger')->crit(sprintf('[cms::getBlockService] block.id=%d - service:%s does not exists', $block->getId(), $id));
        }

        return false;
    }

    /**
     * return a fully loaded page ( + blocks ) from a route name
     *
     * if the page does not exists then the page is created.
     *
     * @param  $route_name
     * @return Application\Sonata\PageBundle\Entity\Page|bool
     */
    public function getPageByRouteName($route_name)
    {

        if(!isset($this->route_pages[$route_name])) {
            $em = $this->container->get('doctrine.orm.default_entity_manager');
            $pages = $em->createQueryBuilder()
                ->select('p, t')
                ->from('Application\Sonata\PageBundle\Entity\Page', 'p')
                ->where('p.route_name = :route_name')
                ->leftJoin('p.template', 't')
                ->setParameters(array(
                    'route_name' => $route_name
                ))
                ->getQuery()
                ->execute();

            $page = count($pages) > 0 ? $pages[0] : false;

            if(!$page) {
                // create a new page for this routing
                $page = new Page;
                $page->setTemplate($this->getDefaultTemplate());
                $page->setEnabled(true);
                $page->setRouteName($route_name);
                $page->setName($route_name);
                $page->setLoginRequired(false);
                $page->setCreatedAt(new \DateTime);
                $page->setUpdatedAt(new \DateTime);

                $em->persist($page);
                $em->flush();
            }

            $this->loadBlocks($page);


            $this->route_pages[$route_name] = $page;
        }

        return $this->route_pages[$route_name];
    }

    /**
     * return the default template used in the current application
     *
     * @return bool | Application\Sonata\PageBundle\Entity\Template
     */
    public function getDefaultTemplate()
    {
        $templates = $this->container->get('doctrine.orm.default_entity_manager')
            ->createQueryBuilder()
            ->select('t')
            ->from('Application\Sonata\PageBundle\Entity\Template', 't')
            ->where('t.id = :id')
            ->setParameters(array(
                 'id' => 1
            ))
            ->getQuery()
            ->execute();

        return count($templates) > 0 ? $templates[0] : false;
    }

    /**
     * return a fully loaded CMS page ( + blocks ) 
     *
     * @param  $slug
     * @return bool
     */
    public function getPageBySlug($slug)
    {

        $pages = $this->container->get('doctrine.orm.default_entity_manager')
            ->createQueryBuilder()
            ->select('p')
            ->from('Application\Sonata\PageBundle\Entity\Page', 'p')
            ->leftJoin('p.template', 't')
            ->where('p.slug = :slug')
            ->setParameters(array(
                'slug' => $slug
            ))
            ->getQuery()
            ->execute();

        $page = count($pages) > 0 ? $pages[0] : false;

        if($page) {
            $this->loadBlocks($page);
        }

        return $page;
    }

    /**
     * return the current page
     *
     * if the current route linked to a CMS page ( route name = `page_slug`)
     *   then the page is retrieve by using a slug
     *   otherwise the page is loaded from the route name
     *
     * @return
     */
    public function getCurrentPage()
    {

        if($this->current_page === null) {

            $route_name = $this->container->get('request')->get('_route');

            if($route_name == 'page_slug') { // true cms page
                $slug = $this->container->get('request')->get('slug');
                
                $this->current_page = $this->getPageBySlug($slug);

                if(!$this->current_page) {
                    $this->container->get('logger')->crit(sprintf('[page:getCurrentPage] no page available for slug : %s', $slug));
                }

            } else { // hybrid page, ie an action is used
                $this->current_page = $this->getPageByRouteName($route_name);

                if(!$this->current_page) {
                    $this->container->get('logger')->crit(sprintf('[page:getCurrentPage] no page available for route : %s', $route_name));
                }
            }
        }

        return $this->current_page;
    }

    public function getBlock($id)
    {
        if(!isset($this->blocks[$id])) {

            $blocks = $this->container->get('doctrine.orm.default_entity_manager')
                ->createQueryBuilder()
                ->select('b')
                ->from('Application\Sonata\PageBundle\Entity\Block', 'b')
                ->where('b.id = :id')
                ->setParameters(array(
                  'id' => $id
                ))
                ->getQuery()
                ->execute();

            $block = count($blocks) > 0 ? $blocks[0] : false;

            $this->blocks[$id] = $block;
        }

        return $this->blocks[$id];
    }

    /**
     * load all the related nested blocks linked to one page.
     *
     * @param  $page
     * @return void
     */
    public function loadBlocks($page)
    {

        $blocks = $this->container->get('doctrine.orm.default_entity_manager')
            ->createQuery('SELECT b FROM Application\Sonata\PageBundle\Entity\Block b INDEX BY b.id WHERE b.page = :page ORDER BY b.position ASC')
            ->setParameters(array(
                 'page' => $page->getId()
            ))
            ->execute();

        $page->disableBlockLazyLoading();

        foreach($blocks as $block) {


            $parent = $block->getParent();

            $block->disableChildrenLazyLoading();
            if(!$parent) {
                $page->addBlocks($block);

                continue;
            }

//            var_dump(sprintf('parent(%d)->addChild(%d)', $block->getParent()->getId(), $block->getId()));

            $blocks[$block->getParent()->getId()]->disableChildrenLazyLoading();
            $blocks[$block->getParent()->getId()]->addChildren($block);

            $this->blocks[$block->getId()] = $block;
        }
    }


    public function defineBlockForm($form)
    {
        $form->setValidationGroups(array($form->getData()->getType()));

        $form->add(new \Symfony\Component\Form\CheckboxField('enabled'));

        $group_field = new \Symfony\Component\Form\FieldGroup('settings');
        $form->add($group_field);

        $this->getBlockService($form->getData())->defineBlockGroupField($group_field, $form->getData());
    }
    /**
     * save the block order from the page disposition
     *
     * Format :
     *      Array
     *      (
     *          [cms-block-2] => Array
     *              (
     *                  [type] => core.container
     *                  [child] => Array
     *                      (
     *                          [cms-block-4] => Array
     *                              (
     *                                  [type] => core.action
     *                                  [child] =>
     *                              )
     *
     *                      )
     *
     *              )
     *
     *          [cms-block-5] => Array
     *              (
     *                  [type] => core.container
     *                  [child] =>
     *              )
     *
     *          [cms-block-8] => Array
     *              (
     *                  [type] => core.container
     *                  [child] => Array
     *                      (
     *                          [cms-block-9] => Array
     *                              (
     *                                  [type] => core.container
     *                                  [child] => Array
     *                                      (
     *                                          [cms-block-3] => Array
     *                                              (
     *                                                  [type] => core.text
     *                                                  [child] =>
     *                                              )
     *
     *                                      )
     *
     *                              )
     *
     *                      )
     *
     *              )
     *
     *      )
     *
     * @param  $data
     * @return void
     */
    public function savePosition($data)
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        
        $em->getConnection()->beginTransaction();
        
        try {
            foreach($data as $code => $block) {

                $parent_id = (int) substr($code, 10);

                $block['child'] = (isset($block['child']) && is_array($block['child'])) ? $block['child'] : array();

                $this->saveNestedPosition($block['child'], $parent_id, $em);
            }

        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            return false;
        }

         $em->getConnection()->commit();

         return true;
    }

    /**
     * Save block by re attaching a page to the correct page and correct block's parent.
     *
     * @param  $blocks
     * @param  $parent_id
     * @param  $entity_manager
     * @return
     */
    protected function saveNestedPosition($blocks, $parent_id, $entity_manager)
    {

        if(!is_array($blocks)) {
            return;
        }

        $table_name = $entity_manager->getClassMetadata('Application\Sonata\PageBundle\Entity\Block')->table['name'];

        $position = 1;
        foreach($blocks as $code => $block) {
            $block_id = (int) substr($code, 10);

            $sql = sprintf('UPDATE %s child, (SELECT p.page_id as page_id FROM %s p WHERE id = %d ) as parent SET child.position = %d, child.parent_id = %d, child.page_id = parent.page_id WHERE child.id = %d',
                $table_name,
                $table_name,
                $parent_id,
                $position,
                $parent_id,
                $block_id
            );

            $entity_manager
                ->getConnection()
                ->exec($sql);

            $block['child'] = (isset($block['child']) && is_array($block['child'])) ? $block['child'] : array();

            $this->saveNestedPosition($block['child'], $block_id, $entity_manager);

            $position++;
        }
    }

    public function setBlocks($blocks)
    {
        $this->blocks = $blocks;
    }

    public function getBlocks()
    {
        return $this->blocks;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }
    
    public function setRoutePages($route_pages)
    {
        $this->route_pages = $route_pages;
    }

    public function getRoutePages()
    {
        return $this->route_pages;
    }
}