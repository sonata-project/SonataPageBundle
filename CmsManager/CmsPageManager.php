<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\CmsManager;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\BlockManagerInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Block\BlockServiceInterface;
use Sonata\PageBundle\Cache\CacheInterface;
use Sonata\PageBundle\Cache\Invalidation\InvalidationInterface;
use Sonata\PageBundle\Cache\CacheElement;
use Sonata\AdminBundle\Admin\AdminInterface;

/**
 * The Manager class is in charge of retrieving the correct page (cms page or action page)
 *
 * An action page is linked to a symfony action and a cms page is a standalone page.
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CmsPageManager extends BaseCmsPageManager
{
    protected $blockManager;

    protected $templating;

    protected $pageAdmin;

    protected $blockAdmin;

    protected $cacheInvalidation;

    public function __construct(
      PageManagerInterface $pageManager,
      BlockManagerInterface $blockManager,
      EngineInterface $templating,
      InvalidationInterface $cacheInvalidation
  )
    {
        $this->pageManager        = $pageManager;
        $this->blockManager       = $blockManager;
        $this->templating         = $templating;
        $this->cacheInvalidation  = $cacheInvalidation;
    }

    public function getCode()
    {
        return 'page';
    }

    public function renderPage(PageInterface $page, array $params = array(), Response $response = null)
    {
        $template = 'SonataPageBundle::layout.html.twig';
        if ($this->getCurrentPage()) {
            $template = $this->getCurrentPage()->getTemplate()->getPath();
        }

        $params['page']         = $page;
        $params['manager']      = $this;
        $params['page_admin']   = $this->getPageAdmin();
        $params['block_admin']  = $this->getBlockAdmin();

        $response = $this->templating->renderResponse($template, $params, $response);
        $response->setTtl($page->getTtl());

        return $response;
    }

    /**
     * Return a PageInterface instance depends on the $page argument
     *
     * @param mixed $page
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getPage($page)
    {
        if (is_string($page)) { // page is a slug, load the related page
            $page = $this->getPageByRouteName($page);
        } else if ( is_numeric($page)) {
            $page = $this->getPageById($page);
        } else if (!$page) { // get the current page
            $page = $this->getCurrentPage();
        }

        if (!$page instanceof PageInterface) {
            throw new \RunTimeException('Unable to retrieve the page');
        }

        return $page;
    }

    /**
     * @param string $name
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param null|\Sonata\PageBundle\Model\BlockInterface $parentContainer
     * @return bool|null|\Sonata\PageBundle\Model\BlockInterface
     */
    public function findContainer($name, PageInterface $page, BlockInterface $parentContainer = null)
    {
        $container = false;

        if ($parentContainer) {
            // parent container is set, nothing to find, don't need to loop across the
            // name to find the correct container (main template level)
            $container = $parentContainer;
        }

        // first level blocks are containers
        if (!$container && $page->getBlocks()) {
            foreach ($page->getBlocks() as $block) {
                if ($block->getSetting('name') == $name) {

                    $container = $block;
                    break;
                }
            }
        }

        if (!$container) {
            $container = $this->blockManager->createNewContainer(array(
                'enabled' => true,
                'page' => $page,
                'name' => $name,
                'position' => 1
            ));

            if ($parentContainer) {
                $container->setParent($parentContainer);
            }

            $this->blockManager->save($container);
        }

        return $container;
    }

    /**
     * return a fully loaded page ( + blocks ) from a route name
     *
     * if the page does not exists then the page is created.
     *
     * @param string $url
     * @return Application\Sonata\PageBundle\Model\PageInterface
     */
    public function getPageByUrl($url)
    {
        $page = $this->getPageManager()->getPageByUrl($url);

        if ($page) {
            $this->loadBlocks($page);
        }

        return $page;
    }

    /**
     * return a fully loaded page ( + blocks ) from a route name
     *
     * if the page does not exists then the page is created.
     *
     * @param string $routeName
     * @param boolean $create
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getPageByRouteName($routeName, $create = true)
    {
        if (!isset($this->routePages[$routeName])) {
            $page = $this->getPageManager()->getPageByName($routeName);

            if (!$page && !$create) {
                throw new \RuntimeException(sprintf('Unable to find the page : %s', $routeName));
            } else if (!$page) {
                $page = $this->createPage($routeName);
            }

            $this->loadBlocks($page);
            $this->routePages[$routeName] = $page;
        }

        return $this->routePages[$routeName];
    }

    /**
     * return a fully loaded page ( + blocks ) from a route name
     *
     * if the page does not exists then the page is created.
     *
     * @param integer $id
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getPageById($id)
    {
        $page = $this->getPageManager()->findOneBy(array('id' => $id));

        $this->loadBlocks($page);

        return $page;
    }

    /**
     * @throws \RuntimeException
     * @param string $routeName
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function createPage($routeName)
    {
        if (!$this->getDefaultTemplate()) {
            throw new \RuntimeException('No default template defined');
        }

        $page = $this->getPageManager()->createNewPage(array(
            'template' => $this->getDefaultTemplate(),
            'enabled'  => true,
            'routeName' => $routeName,
            'name'      => $routeName,
        ));

        $this->getManager()->save($page);

        return $page;
    }

    /**
     * return the default template used in the current application
     *
     * @return bool | Application\Sonata\PageBundle\Entity\Template
     */
    public function getDefaultTemplate()
    {
        return $this->getPageManager()->getDefaultTemplate();
    }

    /**
     *
     * @param string $id
     * @return \Sonata\PageBundle\Model\BlockInterface
     */
    public function getBlock($id)
    {
        if (!isset($this->blocks[$id])) {
            $this->blocks[$id] = $this->blockManager->getBlock($id);
        }

        return $this->blocks[$id];
    }

    /**
     * load all the related nested blocks linked to one page.
     *
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @return void
     */
    private function loadBlocks(PageInterface $page)
    {
        $blocks = $this->blockManager->loadPageBlocks($page);

        // save a local cache
        foreach ($blocks as $block) {
            $this->blocks[$block->getId()] = $block;
        }
    }

    /**
     * @return \Sonata\PageBundle\Model\BlockManagerInterface
     */
    public function getBlockManager()
    {
        return $this->blockManager;
    }

    public function setBlockAdmin(AdminInterface $blockAdmin)
    {
        $this->blockAdmin = $blockAdmin;
    }

    public function getBlockAdmin()
    {
        return $this->blockAdmin;
    }

    public function setPageAdmin(AdminInterface $pageAdmin)
    {
        $this->pageAdmin = $pageAdmin;
    }

    public function getPageAdmin()
    {
        return $this->pageAdmin;
    }
}