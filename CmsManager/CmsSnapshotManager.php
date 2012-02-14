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

use Sonata\BlockBundle\Model\BlockInterface;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\SnapshotPageProxy;
use Sonata\PageBundle\Util\RecursiveBlockIterator;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Exception\PageNotFoundException;


/**
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CmsSnapshotManager extends BaseCmsPageManager
{
    protected $pageManager;

    protected $pageReferences = array();

    protected $pages = array();

    /**
     * @param array $httpErrorCodes
     * @param \Sonata\PageBundle\Model\SnapshotManagerInterface $pageManager
     */
    public function __construct(array $httpErrorCodes = array(), SnapshotManagerInterface $pageManager)
    {
        parent::__construct($httpErrorCodes);

        $this->pageManager = $pageManager;
    }

    /**
     * Return a PageInterface instance depends on the $page argument
     *
     * @throws \Sonata\PageBundle\Exception\PageNotFoundException
     * @param \Sonata\PageBundle\Model\SiteInterface $site
     * @param $page
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
            throw new PageNotFoundException('Unable to retrieve the snapshot');
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
     * Return a fully loaded page ( + blocks ) whose match with the $value of the $fieldName
     *
     * @throws \Sonata\PageBundle\Exception\PageNotFoundException
     * @param null|\Sonata\PageBundle\Model\SiteInterface $site
     * @param $fieldName
     * @param $value
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

            $snapshot = $this->pageManager->findEnableSnapshot($parameters);

            if (!$snapshot) {
                throw new PageNotFoundException();
            }

            $page = new SnapshotPageProxy($this->pageManager, $snapshot);

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
    private function loadBlocks(PageInterface $page)
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
}