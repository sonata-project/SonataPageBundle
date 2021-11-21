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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\CmsManager\CmsPageManager;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Model\Block as AbstractBlock;
use Sonata\PageBundle\Model\BlockInteractorInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Tests\Model\Page;
use Sonata\PageBundle\Tests\Model\Site;

class CmsBlock extends AbstractBlock
{
    public function setId($id)
    {
    }

    public function getId()
    {
    }
}

/**
 * Test CmsPageManager.
 */
class CmsPageManagerTest extends TestCase
{
    /**
     * @var MockObject&BlockInteractorInterface
     */
    private $blockInteractor;

    /**
     * @var MockObject&PageManagerInterface
     */
    private $pageManager;

    /**
     * @var CmsPageManager
     */
    private $manager;

    /**
     * Setup manager object to test.
     */
    protected function setUp(): void
    {
        $this->blockInteractor = $this->getMockBlockInteractor();
        $this->pageManager = $this->createMock(PageManagerInterface::class);
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

        static::assertSame(
            spl_object_hash($block),
            spl_object_hash($container),
            'should retrieve the block of the page'
        );
    }

    /**
     * Test finding an non-existing container in a page does create a new block.
     */
    public function testFindNonExistingContainerCreatesNewBlock(): void
    {
        $page = new Page();

        $container = $this->manager->findContainer('newcontainer', $page);

        static::assertInstanceOf(PageBlockInterface::class, $container, 'should be a block');
        static::assertSame('newcontainer', $container->getSetting('code'));
    }

    /**
     * Test get Page method with url return Page.
     */
    public function testGetPageWithUrl(): void
    {
        $pageManager = $this->createMock(PageManagerInterface::class);

        $pageManager->method('findOneBy')->willReturn(new Page());
        $this->blockInteractor->method('loadPageBlocks')->willReturn([]);

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = '/test';
        $site = new Site();

        static::assertInstanceOf(PageInterface::class, $manager->getPage($site, $page));
    }

    /**
     * Test get Page method with url throw Exception.
     */
    public function testGetPageWithUrlException(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Unable to find the page : url = /test');

        $pageManager = $this->createMock(PageManagerInterface::class);

        $this->blockInteractor->method('loadPageBlocks')->willReturn([]);

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = '/test';
        $site = new Site();

        $pageManager->method('findOneBy')->willReturn(null);
        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $manager->getPage($site, $page);
    }

    /**
     * Test get Page method with url return Page.
     */
    public function testGetPageWithRouteName(): void
    {
        $pageManager = $this->createMock(PageManagerInterface::class);

        $pageManager->method('findOneBy')->willReturn(new Page());
        $this->blockInteractor->method('loadPageBlocks')->willReturn([]);

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = 'test';
        $site = new Site();

        static::assertInstanceOf(PageInterface::class, $manager->getPage($site, $page));
    }

    /**
     * Test get Page method with url throw Exception.
     */
    public function testGetPageWithRouteNameException(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Unable to find the page : url = /test');

        $pageManager = $this->createMock(PageManagerInterface::class);

        $this->blockInteractor->method('loadPageBlocks')->willReturn([]);

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = '/test';
        $site = new Site();

        $pageManager->method('findOneBy')->willReturn(null);
        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $manager->getPage($site, $page);
    }

    /**
     * Test get Page method with url return Page.
     */
    public function testGetPageWithId(): void
    {
        $pageManager = $this->createMock(PageManagerInterface::class);

        $pageManager->method('findOneBy')->willReturn(new Page());
        $this->blockInteractor->method('loadPageBlocks')->willReturn([]);

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = 1;
        $site = new Site();

        static::assertInstanceOf(PageInterface::class, $manager->getPage($site, $page));
    }

    /**
     * Test get Page method with url throw Exception.
     */
    public function testGetPageWithIdException(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Unable to find the page : id = 1');

        $pageManager = $this->createMock(PageManagerInterface::class);

        $this->blockInteractor->method('loadPageBlocks')->willReturn([]);

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = 1;
        $site = new Site();

        $pageManager->method('findOneBy')->willReturn(null);
        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $manager->getPage($site, $page);
    }

    /**
     * Test get Page method with url return Page.
     */
    public function testGetPageWithoutParam(): void
    {
        $pageManager = $this->createMock(PageManagerInterface::class);

        $pageManager->method('findOneBy')->willReturn(new Page());
        $this->blockInteractor->method('loadPageBlocks')->willReturn([]);

        $manager = $this->createManager($pageManager, $this->blockInteractor);
        $manager->setCurrentPage(new Page());
        $page = null;
        $site = new Site();

        static::assertInstanceOf(PageInterface::class, $manager->getPage($site, $page));
    }

    /**
     * Test get Page method with url throw Exception.
     */
    public function testGetPageWithoutParamException(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Unable to retrieve the page');

        $pageManager = $this->createMock(PageManagerInterface::class);

        $this->blockInteractor->method('loadPageBlocks')->willReturn([]);

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $page = null;
        $site = new Site();

        $pageManager->method('findOneBy')->willReturn(null);
        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $manager->getPage($site, $page);
    }

    /**
     * Returns a mock block interactor.
     */
    protected function getMockBlockInteractor(): BlockInteractorInterface
    {
        $callback = static function ($options) {
            $block = new CmsBlock();
            $block->setSettings($options);

            return $block;
        };

        $mock = $this->createMock(BlockInteractorInterface::class);
        $mock->method('createNewContainer')->willReturnCallback($callback);

        return $mock;
    }

    private function createManager($pageManager, $blockInteractor): CmsPageManager
    {
        return new CmsPageManager($pageManager, $blockInteractor);
    }
}
