<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Page;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\CmsManager\CmsPageManager;
use Sonata\PageBundle\Model\Block as AbstractBlock;
use Sonata\PageBundle\Tests\Model\Page;
use Sonata\PageBundle\Tests\Model\Site;

class CmsBlock extends AbstractBlock
{
    public function setId($id): void
    {
    }

    public function getId(): void
    {
    }
}

/**
 * Test CmsPageManager.
 */
class CmsPageManagerTest extends TestCase
{
    /**
     * @var \Sonata\PageBundle\CmsManager\CmsPageManager
     */
    protected $manager;

    /**
     * Setup manager object to test.
     */
    public function setUp(): void
    {
        $this->blockInteractor = $this->getMockBlockInteractor();
        $this->pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');
        $this->manager = new CmsPageManager($this->pageManager, $this->blockInteractor);
    }

    /**
     * Test finding an existing container in a page.
     */
    public function testFindExistingContainer(): void
    {
        $block = new CmsBlock();
        $block->setSettings(['code' => 'findme']);

        $page = new Page();
        $page->addBlocks($block);

        $container = $this->manager->findContainer('findme', $page);

        $this->assertEquals(spl_object_hash($block), spl_object_hash($container),
            'should retrieve the block of the page');
    }

    /**
     * Test finding an non-existing container in a page does create a new block.
     */
    public function testFindNonExistingContainerCreatesNewBlock(): void
    {
        $page = new Page();

        $container = $this->manager->findContainer('newcontainer', $page);

        $this->assertInstanceOf('Sonata\PageBundle\Model\PageBlockInterface', $container, 'should be a block');
        $this->assertEquals('newcontainer', $container->getSetting('code'));
    }

    /**
     * Test get Page method with url return Page.
     */
    public function testGetPageWithUrl(): void
    {
        $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');

        $pageManager->expects($this->any())->method('findOneBy')->will($this->returnValue(new Page()));
        $this->blockInteractor->expects($this->any())->method('loadPageBlocks')->will($this->returnValue([]));

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = '/test';
        $site = new Site();

        $this->assertInstanceOf('Sonata\PageBundle\Model\PageInterface', $manager->getPage($site, $page));
    }

    /**
     * Test get Page method with url throw Exception.
     */
    public function testGetPageWithUrlException(): void
    {
        $this->expectException(\Sonata\PageBundle\Exception\PageNotFoundException::class);
        $this->expectExceptionMessage('Unable to find the page : url = /test');

        $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');

        $this->blockInteractor->expects($this->any())->method('loadPageBlocks')->will($this->returnValue([]));

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = '/test';
        $site = new Site();

        $pageManager->expects($this->any())->method('findOneBy')->will($this->returnValue(null));
        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $manager->getPage($site, $page);
    }

    /**
     * Test get Page method with url return Page.
     */
    public function testGetPageWithRouteName(): void
    {
        $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');

        $pageManager->expects($this->any())->method('findOneBy')->will($this->returnValue(new Page()));
        $this->blockInteractor->expects($this->any())->method('loadPageBlocks')->will($this->returnValue([]));

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = 'test';
        $site = new Site();

        $this->assertInstanceOf('Sonata\PageBundle\Model\PageInterface', $manager->getPage($site, $page));
    }

    /**
     * Test get Page method with url throw Exception.
     */
    public function testGetPageWithRouteNameException(): void
    {
        $this->expectException(\Sonata\PageBundle\Exception\PageNotFoundException::class);
        $this->expectExceptionMessage('Unable to find the page : url = /test');

        $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');

        $this->blockInteractor->expects($this->any())->method('loadPageBlocks')->will($this->returnValue([]));

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = '/test';
        $site = new Site();

        $pageManager->expects($this->any())->method('findOneBy')->will($this->returnValue(null));
        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $manager->getPage($site, $page);
    }

    /**
     * Test get Page method with url return Page.
     */
    public function testGetPageWithId(): void
    {
        $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');

        $pageManager->expects($this->any())->method('findOneBy')->will($this->returnValue(new Page()));
        $this->blockInteractor->expects($this->any())->method('loadPageBlocks')->will($this->returnValue([]));

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = 1;
        $site = new Site();

        $this->assertInstanceOf('Sonata\PageBundle\Model\PageInterface', $manager->getPage($site, $page));
    }

    /**
     * Test get Page method with url throw Exception.
     */
    public function testGetPageWithIdException(): void
    {
        $this->expectException(\Sonata\PageBundle\Exception\PageNotFoundException::class);
        $this->expectExceptionMessage('Unable to find the page : id = 1');

        $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');

        $this->blockInteractor->expects($this->any())->method('loadPageBlocks')->will($this->returnValue([]));

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = 1;
        $site = new Site();

        $pageManager->expects($this->any())->method('findOneBy')->will($this->returnValue(null));
        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $manager->getPage($site, $page);
    }

    /**
     * Test get Page method with url return Page.
     */
    public function testGetPageWithoutParam(): void
    {
        $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');

        $pageManager->expects($this->any())->method('findOneBy')->will($this->returnValue(new Page()));
        $this->blockInteractor->expects($this->any())->method('loadPageBlocks')->will($this->returnValue([]));

        $manager = $this->createManager($pageManager, $this->blockInteractor);
        $manager->setCurrentPage(new Page());
        $page = null;
        $site = new Site();

        $this->assertInstanceOf('Sonata\PageBundle\Model\PageInterface', $manager->getPage($site, $page));
    }

    /**
     * Test get Page method with url throw Exception.
     */
    public function testGetPageWithoutParamException(): void
    {
        $this->expectException(\Sonata\PageBundle\Exception\PageNotFoundException::class);
        $this->expectExceptionMessage('Unable to retrieve the page');

        $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');

        $this->blockInteractor->expects($this->any())->method('loadPageBlocks')->will($this->returnValue([]));

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = null;
        $site = new Site();

        $pageManager->expects($this->any())->method('findOneBy')->will($this->returnValue(null));
        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $manager->getPage($site, $page);
    }

    /**
     * Returns a mock block interactor.
     *
     * @return \Sonata\PageBundle\Model\BlockInteractorInterface
     */
    protected function getMockBlockInteractor()
    {
        $callback = function ($options) {
            $block = new CmsBlock();
            $block->setSettings($options);

            return $block;
        };

        $mock = $this->createMock('Sonata\PageBundle\Model\BlockInteractorInterface');
        $mock->expects($this->any())->method('createNewContainer')->will($this->returnCallback($callback));

        return $mock;
    }

    private function createManager($pageManager, $blockInteractor)
    {
        return new CmsPageManager($pageManager, $blockInteractor);
    }
}
