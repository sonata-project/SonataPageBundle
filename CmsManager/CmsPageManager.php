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
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Model\BlockInteractorInterface;

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
    protected $blockInteractor;

    protected $pageManager;

    /**
     * @param array $httpErrorCodes
     * @param \Sonata\PageBundle\Model\PageManagerInterface $pageManager
     * @param \Sonata\PageBundle\Model\BlockInteractorInterface $blockInteractor
     */
    public function __construct(array $httpErrorCodes = array(), PageManagerInterface $pageManager, BlockInteractorInterface $blockInteractor)
    {
        parent::__construct($httpErrorCodes);

        $this->pageManager     = $pageManager;
        $this->blockInteractor = $blockInteractor;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return 'page';
    }

    /**
     * {@inheritdoc}
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
            throw new PageNotFoundException('Unable to retrieve the page');
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

        if (!$container) {
            $container = $this->blockInteractor->createNewContainer(array(
                'enabled'  => true,
                'page'     => $page,
                'name'     => $name,
                'position' => 1,
                'parent'   => $parentContainer
            ));
        }

        return $container;
    }

    /**
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
            $this->pages[$id] = false;

            $parameters = array(
                $fieldName => $value,
            );

            if ($site) {
                $parameters['site'] = $site->getId();
            }

            $page = $this->pageManager->findOneBy($parameters);

            if (!$page) {
                throw new PageNotFoundException(sprintf('Unable to find the page : %s = %s', $fieldName, $value));
            }

            $this->loadBlocks($page);
            $id = $page->getId();

            if ($fieldName != 'id') {
                $this->pageReferences[$fieldName][$value] = $id;
            }

            $this->pages[$id] = $page;
        }

        return $this->pages[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function getBlock($id)
    {
        if (!isset($this->blocks[$id])) {
            $this->blocks[$id] = $this->blockInteractor->getBlock($id);
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
        $blocks = $this->blockInteractor->loadPageBlocks($page);

        // save a local cache
        foreach ($blocks as $block) {
            $this->blocks[$block->getId()] = $block;
        }
    }
}