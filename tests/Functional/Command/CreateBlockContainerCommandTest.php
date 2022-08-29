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
use Sonata\PageBundle\Tests\App\Entity\SonataPageBlock;
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

final class CreateBlockContainerCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        static::bootKernel();

        $this->commandTester = new CommandTester(
            (new Application(static::createKernel()))->find('sonata:page:create-block-container')
        );
    }

    public function testThrowExceptionOnNoArguments(): void
    {
        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('Not enough arguments (missing: "templateCode, blockCode").');

        $this->commandTester->execute([]);
    }

    public function testThrowExceptionOnMissingArgument(): void
    {
        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('Not enough arguments (missing: "blockCode").');

        $this->commandTester->execute(['templateCode' => 'default']);
    }

    public function testCreateBlockContainer(): void
    {
        $this->prepareData();

        static::assertSame(0, $this->countBlocks());

        $this->commandTester->execute([
            'templateCode' => 'default',
            'blockCode' => 'content_bottom',
        ]);

        static::assertSame(1, $this->countBlocks());

        static::assertStringContainsString('done!', $this->commandTester->getDisplay());
    }

    public function testCreateBlockContainerWithName(): void
    {
        $this->prepareData();

        static::assertSame(0, $this->countBlocks());

        $this->commandTester->execute([
            'templateCode' => 'default',
            'blockCode' => 'content_bottom',
            'blockName' => 'Content Bottom',
        ]);

        static::assertSame(1, $this->countBlocks());

        static::assertStringContainsString('done!', $this->commandTester->getDisplay());
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

        $page = new SonataPagePage();
        $page->setName('name');
        $page->setTemplateCode('default');

        $page2 = new SonataPagePage();
        $page2->setName('name');
        $page2->setTemplateCode('random');

        $manager->persist($page);
        $manager->persist($page2);

        $manager->flush();
    }

    /**
     * @psalm-suppress UndefinedPropertyFetch
     */
    private function countBlocks(): int
    {
        // TODO: Simplify this when dropping support for Symfony 4.
        // @phpstan-ignore-next-line
        $container = method_exists(static::class, 'getContainer') ? static::getContainer() : static::$container;
        $manager = $container->get('doctrine.orm.entity_manager');
        \assert($manager instanceof EntityManagerInterface);

        return $manager->getRepository(SonataPageBlock::class)->count([]);
    }
}
