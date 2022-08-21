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

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Sonata\PageBundle\Entity\BlockInteractor;
use Sonata\PageBundle\Model\BlockManagerInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Tests\App\Entity\SonataPageBlock;
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class BlockInteractorTest extends KernelTestCase
{
    private ObjectManager $entityManager;

    private BlockInteractor $blockInteractor;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();

        $registry = $kernel->getContainer()->get('doctrine');
        \assert($registry instanceof ManagerRegistry);
        $blockManager = $kernel->getContainer()->get('sonata.page.manager.block');
        \assert($blockManager instanceof BlockManagerInterface);

        $this->entityManager = $registry->getManager();
        $this->blockInteractor = new BlockInteractor($registry, $blockManager);
    }

    public function testLoadPageBlocks(): void
    {
        $page = $this->prepareData();
        $block = $page->getBlocks()->first();

        $blocks = $this->blockInteractor->loadPageBlocks($page);

        static::assertNotFalse($block);
        static::assertSame([1 => $block], $blocks);
    }

    public function testNotLoadBlocks(): void
    {
        $page = $this->prepareData();

        static::assertCount(1, $this->blockInteractor->loadPageBlocks($page));
        static::assertCount(0, $this->blockInteractor->loadPageBlocks($page));
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
        $page->addBlock($block);
        $page->setTemplateCode('TemplateCode');

        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }
}
