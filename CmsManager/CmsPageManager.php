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
use Sonata\AdminBundle\Validator\ErrorElement;

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

    protected function getRenderPageParams(PageInterface $page)
    {
        return array_merge(parent::getRenderPageParams($page), array(
            'page_admin'    => $this->getPageAdmin(),
            'block_admin'   => $this->getBlockAdmin(),
        ));
    }

    /**
     * Return a PageInterface instance depends on the $page argument
     *
     * @param mixed $page
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getPage($page)
    {
        if (is_string($page) && substr($page, 0, 1) == '/') {
            $page = $this->getPageByUrl($page);
        } else if (is_string($page)) { // page is a slug, load the related page
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
        $page = $this->getPageManager()->createNewPage(array(
            'routeName' => $routeName,
            'name'      => $routeName,
        ));

        $this->getPageManager()->save($page);

        return $page;
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

    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        if (!$block->getId() && !$block->getType()) {
            return;
        }

        $service = $this->getBlockService($block);
        $service->validateBlock($errorElement, $block);
    }
}