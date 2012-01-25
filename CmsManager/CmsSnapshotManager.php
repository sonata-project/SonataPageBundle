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

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\SnapshotPageProxy;
use Sonata\PageBundle\Block\BlockServiceInterface;
use Sonata\PageBundle\Cache\CacheInterface;
use Sonata\PageBundle\Util\RecursiveBlockIterator;
use Sonata\PageBundle\Cache\CacheElement;
use Sonata\PageBundle\Cache\Invalidation\InvalidationInterface;
use Sonata\PageBundle\Model\SiteInterface;

use Sonata\AdminBundle\Admin\AdminInterface;

/**
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CmsSnapshotManager extends BaseCmsPageManager
{
    /**
     * @param \Sonata\PageBundle\Model\SnapshotManagerInterface $pageManager
     * @param \Symfony\Component\Templating\EngineInterface $templating
     * @param \Sonata\PageBundle\Cache\Invalidation\InvalidationInterface $cacheInvalidation
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct(
        EngineInterface $templating,
        InvalidationInterface $cacheInvalidation,
        RouterInterface $router,
        array $httpErrorCodes = array(),
        SnapshotManagerInterface $pageManager
    )
    {
        parent::__construct($templating, $cacheInvalidation, $router, $httpErrorCodes);

        $this->pageManager = $pageManager;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'snapshot';
    }

    /**
     * Return a PageInterface instance depends on the $page argument
     *
     * @param mixed $page
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getPage(SiteInterface $site, $page)
    {
        if (is_string($page) && substr($page, 0, 1) == '/') {
            $page = $this->getPageByUrl($site, $page);
        } else if (is_string($page)) { // page is a slug, load the related page
            $page = $this->getPageByRouteName($site, $page);
        } else if ( is_numeric($page)) {
            $page = $this->getPageById($page);
        } else if (!$page) { // get the current page
            $page = $this->getCurrentPage();
        }

        if (!$page instanceof PageInterface) {
            throw new \RunTimeException('Unable to retrieve the snapshot');
        }

        return $page;
    }

    /**
     * {@inheritdoc}
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

        return $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageByUrl(SiteInterface $site, $url)
    {
        return $this->getPageBy($site, 'url', $url);
    }

    /**
     * {@inheritdoc}
     */
    public function getPageById($id)
    {
        return $this->getPageBy(null, 'pageId', $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getPageByRouteName(SiteInterface $site, $routeName, $create = true)
    {
        return $this->getPageBy($site, 'routeName', $routeName);
    }

    /**
     * {@inheritdoc}
     */
    public function getPageByName(SiteInterface $site, $name, $create = true)
    {
        return $this->getPageBy($site, 'name', $name);
    }

    /**
     * {@inheritdoc}
     */
    public function renderContainer(SiteInterface $site, $name, $page = null, BlockInterface $parentContainer = null)
    {
        try {
            $response = parent::renderContainer($site, $name, $page, $parentContainer);

            return $response;
        } catch(\RunTimeException $e) {

            // silently fail error message
            return '';
        }

    }

    /**
     * return a fully loaded page ( + blocks ) whose match with the $value of the $fieldName
     *
     * @param string $fieldName
     * @param string $value
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    protected function getPageBy(SiteInterface $site = null, $fieldName, $value)
    {
        if ('id' == $fieldName) {
            $id = $value;
        } elseif(isset($this->pageReferences[$fieldName][$value])) {
            $id = $this->pageReferences[$fieldName][$value];
        } else {
            $id = null;
        }

        if (null === $id || !isset($this->pages[$id])) {
            $parameters = array($fieldName => $value);

            if ($site) {
                $parameters['site'] = $site->getId();
            }

            $snapshot = $this->getPageManager()->findEnableSnapshot($parameters);

            if (!$snapshot) {
                throw new NotFoundHttpException(sprintf('Unable to find the snapshot : %s', $value));
            }

            $page = new SnapshotPageProxy($this->getPageManager(), $snapshot);

            $this->pages[$id] = false;

            if ($page) {
               $this->loadBlocks($page);

                $id = $page->getId();

                if ($fieldName != 'id') {
                    $this->pageReferences[$fieldName][$value] = $id;
                }

                $this->pages[$id] = $page;
            }
        }

        return $this->pages[$id];
    }

    /**
     * load the blocks of the $page
     *
     * @param \Sonata\PageBundle\Model\PageInterface $page
     */
    public function loadBlocks(PageInterface $page)
    {
        $i = new RecursiveBlockIterator($page->getBlocks());

        foreach ($i as $block) {
            $this->blocks[$block->getId()] = $block;
        }
    }

    /**
     * @param integer $id
     * @return array|null
     */
    public function getBlock($id)
    {
        if (isset($this->blocks[$id])) {
            return $this->blocks[$id];
        }

        return null;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }
}