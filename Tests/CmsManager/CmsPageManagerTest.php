<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Page;

use Sonata\PageBundle\CmsManager\CmsPageManager;
use Sonata\PageBundle\Model\Block as AbtractBlock;
use Sonata\PageBundle\Tests\Model\Page;
use Sonata\PageBundle\Tests\Model\Site;
use Sonata\PageBundle\Model\BlockInteractorInterface;

class CmsBlock extends AbtractBlock
{
    public function setId($id)
    {}

    public function getId()
    {}
}

/**
 * Test CmsPageManager
 */
class CmsPageManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Sonata\PageBundle\CmsManager\CmsPageManager
     */
    protected $manager;

    /**
     * Setup manager object to test
     */
    public function setUp()
    {
        $this->blockInteractor = $this->getMockBlockInteractor();
        $this->pageManager  = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');
        $this->manager = new CmsPageManager($this->pageManager, $this->blockInteractor);
    }

    /**
     * Test finding an existing container in a page
     */
    public function testFindExistingContainer()
    {
        $block = new CmsBlock();
        $block->setSettings(array('code' => 'findme'));

        $page = new Page();
        $page->addBlocks($block);

        $container = $this->manager->findContainer('findme', $page);

        $this->assertEquals(spl_object_hash($block), spl_object_hash($container),
            'should retrieve the block of the page');
    }

    /**
     * Test finding an non-existing container in a page does create a new block
     */
    public function testFindNonExistingContainerCreatesNewBlock()
    {
        $page = new Page();

        $container = $this->manager->findContainer('newcontainer', $page);

        $this->assertInstanceOf('Sonata\PageBundle\Model\PageBlockInterface', $container, 'should be a block');
        $this->assertEquals('newcontainer', $container->getSetting('code'));
    }

    /**
     * Test get Page method with url return Page
     */
    public function testGetPageWithUrl()
    {
        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');

        $pageManager->expects($this->any())->method('findOneBy')->will($this->returnValue(new Page()));
        $this->blockInteractor->expects($this->any())->method('loadPageBlocks')->will($this->returnValue(array()));

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = "/test";
        $site = new Site();

        $this->assertInstanceOf('Sonata\PageBundle\Model\PageInterface', $manager->getPage($site, $page));
    }

    /**
     * Test get Page method with url throw Exception
     *
     * @expectedException        \Sonata\PageBundle\Exception\PageNotFoundException
     * @expectedExceptionMessage Unable to find the page : url = /test
     *
     */
    public function testGetPageWithUrlException()
    {
        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');

        $this->blockInteractor->expects($this->any())->method('loadPageBlocks')->will($this->returnValue(array()));

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = "/test";
        $site = new Site();

        $pageManager->expects($this->any())->method('findOneBy')->will($this->returnValue(null));
        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $manager->getPage($site, $page);
    }

    /**
     * Test get Page method with url return Page
     */
    public function testGetPageWithRouteName()
    {
        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');

        $pageManager->expects($this->any())->method('findOneBy')->will($this->returnValue(new Page()));
        $this->blockInteractor->expects($this->any())->method('loadPageBlocks')->will($this->returnValue(array()));

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = "test";
        $site = new Site();

        $this->assertInstanceOf('Sonata\PageBundle\Model\PageInterface', $manager->getPage($site, $page));
    }

    /**
     * Test get Page method with url throw Exception
     *
     * @expectedException        \Sonata\PageBundle\Exception\PageNotFoundException
     * @expectedExceptionMessage Unable to find the page : url = /test
     *
     */
    public function testGetPageWithRouteNameException()
    {
        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');

        $this->blockInteractor->expects($this->any())->method('loadPageBlocks')->will($this->returnValue(array()));

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = "/test";
        $site = new Site();

        $pageManager->expects($this->any())->method('findOneBy')->will($this->returnValue(null));
        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $manager->getPage($site, $page);
    }

    /**
     * Test get Page method with url return Page
     */
    public function testGetPageWithId()
    {
        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');

        $pageManager->expects($this->any())->method('findOneBy')->will($this->returnValue(new Page()));
        $this->blockInteractor->expects($this->any())->method('loadPageBlocks')->will($this->returnValue(array()));

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = 1;
        $site = new Site();

        $this->assertInstanceOf('Sonata\PageBundle\Model\PageInterface', $manager->getPage($site, $page));
    }

    /**
     * Test get Page method with url throw Exception
     *
     * @expectedException        \Sonata\PageBundle\Exception\PageNotFoundException
     * @expectedExceptionMessage Unable to find the page : id = 1
     *
     */
    public function testGetPageWithIdException()
    {
        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');

        $this->blockInteractor->expects($this->any())->method('loadPageBlocks')->will($this->returnValue(array()));

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = 1;
        $site = new Site();

        $pageManager->expects($this->any())->method('findOneBy')->will($this->returnValue(null));
        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $manager->getPage($site, $page);
    }

    /**
     * Test get Page method with url return Page
     */
    public function testGetPageWithoutParam()
    {
        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');

        $pageManager->expects($this->any())->method('findOneBy')->will($this->returnValue(new Page()));
        $this->blockInteractor->expects($this->any())->method('loadPageBlocks')->will($this->returnValue(array()));

        $manager = $this->createManager($pageManager, $this->blockInteractor);
        $manager->setCurrentPage(new Page());
        $page = null;
        $site = new Site();

        $this->assertInstanceOf('Sonata\PageBundle\Model\PageInterface', $manager->getPage($site, $page));
    }

    /**
     * Test get Page method with url throw Exception
     *
     * @expectedException        \Sonata\PageBundle\Exception\PageNotFoundException
     * @expectedExceptionMessage Unable to retrieve the page
     *
     */
    public function testGetPageWithoutParamException()
    {
        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');

        $this->blockInteractor->expects($this->any())->method('loadPageBlocks')->will($this->returnValue(array()));

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = null;
        $site = new Site();

        $pageManager->expects($this->any())->method('findOneBy')->will($this->returnValue(null));
        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $manager->getPage($site, $page);
    }

    /**
     * Returns a mock block interactor
     *
     * @return \Sonata\PageBundle\Model\BlockInteractorInterface
     */
    protected function getMockBlockInteractor()
    {
        $callback = function($options) {
            $block = new CmsBlock;
            $block->setSettings($options);

            return $block;
        };

        $mock = $this->getMock('Sonata\PageBundle\Model\BlockInteractorInterface');
        $mock->expects($this->any())->method('createNewContainer')->will($this->returnCallback($callback));

        return $mock;
    }

    private function createManager($pageManager, $blockInteractor)
    {
        return new CmsPageManager($pageManager, $blockInteractor);
    }
}
