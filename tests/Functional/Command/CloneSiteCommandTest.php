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

namespace Sonata\PageBundle\Tests\Functional\Command;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\PageBundle\Tests\App\AppKernel;
use Sonata\PageBundle\Tests\App\Entity\SonataPageBlock;
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSite;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class CloneSiteCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        static::bootKernel();

        $this->commandTester = new CommandTester(
            (new Application(static::createKernel()))->find('sonata:page:clone-site')
        );
    }

    public function testThrowExceptionOnInvalidSourceId(): void
    {
        $this->prepareData();

        static::expectException(\InvalidArgumentException::class);
        static::expectExceptionMessage('Please provide a "--source-id=SITE_ID" option.');

        $this->commandTester->execute([]);
    }

    public function testThrowExceptionOnInvalidDestId(): void
    {
        static::expectException(\InvalidArgumentException::class);
        static::expectExceptionMessage('Please provide a "--dest-id=SITE_ID" option.');

        $this->commandTester->execute(['--source-id' => 1]);
    }

    public function testThrowExceptionOnInvalidPrefix(): void
    {
        static::expectException(\InvalidArgumentException::class);
        static::expectExceptionMessage('Please provide a "--prefix=PREFIX" option.');

        $this->commandTester->execute([
            '--source-id' => 1,
            '--dest-id' => 2,
        ]);
    }

    public function testCloneSite(): void
    {
        $this->prepareData();

        static::assertSame(3, $this->countPages());

        $this->commandTester->execute([
            '--source-id' => 1,
            '--dest-id' => 2,
            '--prefix' => '[CLONED] ',
        ]);

        static::assertSame(6, $this->countPages());

        static::assertStringContainsString('done!', $this->commandTester->getDisplay());
    }

    public function testCloneOnlyHybridPagesFromSite(): void
    {
        $this->prepareData();

        static::assertSame(3, $this->countPages());

        $this->commandTester->execute([
            '--source-id' => 1,
            '--dest-id' => 2,
            '--prefix' => '[CLONED] ',
            '--only-hybrid' => true,
        ]);

        static::assertSame(4, $this->countPages());

        static::assertStringContainsString('done!', $this->commandTester->getDisplay());
    }

    /**
     * @return class-string<KernelInterface>
     */
    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    /**
     * @psalm-suppress UndefinedPropertyFetch
     */
    private function prepareData(): void
    {
        // TODO: Simplify this when dropping support for Symfony 4.
        // @phpstan-ignore-next-line
        $container = method_exists($this, 'getContainer') ? self::getContainer() : self::$container;
        $manager = $container->get('doctrine.orm.entity_manager');
        \assert($manager instanceof EntityManagerInterface);

        $site = new SonataPageSite();
        $site->setName('name');
        $site->setHost('localhost');

        $site2 = new SonataPageSite();
        $site2->setName('cloned_site');
        $site2->setHost('sonata-project.org');

        $page = new SonataPagePage();
        $page->setName('name');
        $page->setTemplateCode('default');
        $page->setSite($site);

        $page2 = new SonataPagePage();
        $page2->setName('child_page');
        $page2->setTemplateCode('default');
        $page2->setParent($page);
        $page2->setSite($site);

        $page3 = new SonataPagePage();
        $page3->setName('hybrid_page');
        $page3->setRouteName('random_route');
        $page3->setTemplateCode('default');
        $page3->setSite($site);

        $parentBlock = new SonataPageBlock();
        $parentBlock->setType('sonata.page.block.container');
        $parentBlock->setPage($page);

        $block = new SonataPageBlock();
        $block->setType('sonata.block.service.text');
        $block->setParent($parentBlock);
        $block->setPage($page);

        $manager->persist($site);
        $manager->persist($site2);
        $manager->persist($page);
        $manager->persist($page2);
        $manager->persist($page3);
        $manager->persist($parentBlock);
        $manager->persist($block);

        $manager->flush();
    }

    /**
     * @psalm-suppress UndefinedPropertyFetch
     */
    private function countPages(): int
    {
        // TODO: Simplify this when dropping support for Symfony 4.
        // @phpstan-ignore-next-line
        $container = method_exists(static::class, 'getContainer') ? static::getContainer() : static::$container;
        $manager = $container->get('doctrine.orm.entity_manager');
        \assert($manager instanceof EntityManagerInterface);

        return $manager->getRepository(SonataPagePage::class)->count([]);
    }
}
