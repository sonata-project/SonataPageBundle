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

namespace Sonata\PageBundle\Tests\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\PageBundle\Entity\BlockInteractor;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Tests\App\AppKernel;
use Sonata\PageBundle\Tests\App\Entity\SonataPageBlock;
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class BlockInteractorTest extends KernelTestCase
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var BlockInteractor
     */
    protected $blockInteractor;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();

        $managerRegistry = $kernel->getContainer()->get('doctrine');
        $blockManager = $kernel->getContainer()->get('sonata.page.manager.block');

        $this->entityManager = $managerRegistry->getManager();
        $this->blockInteractor = new BlockInteractor($managerRegistry, $blockManager);
    }

    /**
     * @testdox It is returning a block list.
     */
    public function testLoadPageBlocks(): void
    {
        $page = $this->prepareData();
        $block = $page->getBlocks()->first();

        $blocks = $this->blockInteractor->loadPageBlocks($page);

        static::assertSame([1 => $block], $blocks);
    }

    /**
     * @testdox it'll return an empty array for blocks that are already loaded.
     */
    public function testNotLoadBlocks(): void
    {
        $page = $this->prepareData();

        static::assertCount(1, $this->blockInteractor->loadPageBlocks($page));
        static::assertCount(0, $this->blockInteractor->loadPageBlocks($page));
    }

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    private function prepareData(): PageInterface
    {
        $block = new SonataPageBlock();
        $block->setId(1);
        $block->setType('Type');

        $this->entityManager->persist($block);

        $page = new SonataPagePage();
        $page->setName('Page name');
        $page->setEnabled(true);
        $page->addBlocks($block);
        $page->setTemplateCode('TemplateCode');

        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }
}
