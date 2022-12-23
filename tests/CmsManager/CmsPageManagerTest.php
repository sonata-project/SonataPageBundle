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

namespace Sonata\PageBundle\Tests\CmsManager;

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

final class CmsBlock extends AbstractBlock
{
}

final class CmsPageManagerTest extends TestCase
{
    /**
     * @var MockObject&BlockInteractorInterface
     */
    private BlockInteractorInterface $blockInteractor;

    /**
     * @var MockObject&PageManagerInterface
     */
    private PageManagerInterface $pageManager;

    private CmsPageManager $manager;

    protected function setUp(): void
    {
        $this->blockInteractor = $this->getMockBlockInteractor();
        $this->pageManager = $this->createMock(PageManagerInterface::class);
        $this->manager = new CmsPageManager($this->pageManager, $this->blockInteractor);
    }

    public function testFindExistingContainer(): void
    {
        $block = new CmsBlock();
        $block->setSettings(['code' => 'findme']);

        $page = new Page();
        $page->addBlock($block);

        $container = $this->manager->findContainer('findme', $page);

        static::assertNotNull($container);
        static::assertSame(
            spl_object_hash($block),
            spl_object_hash($container),
            'should retrieve the block of the page'
        );
    }

    public function testFindNonExistingContainerCreatesNewBlock(): void
    {
        $page = new Page();

        $container = $this->manager->findContainer('newcontainer', $page);

        static::assertInstanceOf(PageBlockInterface::class, $container, 'should be a block');
        static::assertSame('newcontainer', $container->getSetting('code'));
    }

    public function testGetPageWithUrl(): void
    {
        $pageManager = $this->createMock(PageManagerInterface::class);

        $page = new Page();
        $page->setId(42);
        $pageManager->method('findOneBy')->willReturn($page);
        $this->blockInteractor->method('loadPageBlocks')->willReturn([]);

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $site = new Site();
        static::assertInstanceOf(PageInterface::class, $manager->getPage($site, '/test'));
    }

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

    public function testGetPageWithRouteName(): void
    {
        $pageManager = $this->createMock(PageManagerInterface::class);

        $page = new Page();
        $page->setId(42);
        $pageManager->method('findOneBy')->willReturn($page);
        $this->blockInteractor->method('loadPageBlocks')->willReturn([]);

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $site = new Site();
        static::assertInstanceOf(PageInterface::class, $manager->getPage($site, 'test'));
    }

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

    public function testGetPageWithId(): void
    {
        $pageManager = $this->createMock(PageManagerInterface::class);

        $page = new Page();
        $page->setId(42);
        $pageManager->method('findOneBy')->willReturn($page);
        $this->blockInteractor->method('loadPageBlocks')->willReturn([]);

        $manager = $this->createManager($pageManager, $this->blockInteractor);

        $site = new Site();
        static::assertInstanceOf(PageInterface::class, $manager->getPage($site, 1));
    }

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

    public function testGetPageWithoutParam(): void
    {
        $pageManager = $this->createMock(PageManagerInterface::class);

        $page = new Page();
        $page->setId(42);
        $pageManager->method('findOneBy')->willReturn($page);
        $this->blockInteractor->method('loadPageBlocks')->willReturn([]);

        $manager = $this->createManager($pageManager, $this->blockInteractor);
        $manager->setCurrentPage(new Page());

        $site = new Site();
        static::assertInstanceOf(PageInterface::class, $manager->getPage($site, null));
    }

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
     * @return MockObject&BlockInteractorInterface
     */
    private function getMockBlockInteractor(): BlockInteractorInterface
    {
        $mock = $this->createMock(BlockInteractorInterface::class);
        $mock->method('createNewContainer')->willReturnCallback(static function (array $options) {
            $block = new CmsBlock();
            $block->setSettings($options);

            return $block;
        });

        return $mock;
    }

    private function createManager(PageManagerInterface $pageManager, BlockInteractorInterface $blockInteractor): CmsPageManager
    {
        return new CmsPageManager($pageManager, $blockInteractor);
    }
}
