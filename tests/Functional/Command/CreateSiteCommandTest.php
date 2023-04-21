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
use Sonata\PageBundle\Tests\App\Entity\SonataPageSite;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CreateSiteCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        static::bootKernel();

        $this->commandTester = new CommandTester(
            (new Application(static::createKernel()))->find('sonata:page:create-site')
        );
    }

    /**
     * @dataProvider provideSiteCreationOptions
     *
     * @param array{
     *   '--no-confirmation'?: bool,
     *   '--name'?: string,
     *   '--relativePath'?: string,
     *   '--host'?: string,
     *   '--enabledFrom'?: string,
     *   '--enabledTo'?: string,
     *   '--locale'?: string,
     * } $commandInput
     * @param array<string> $questionInputs
     */
    public function testCreateSite(array $commandInput, array $questionInputs, bool $success): void
    {
        $this->commandTester->setInputs($questionInputs);
        $this->commandTester->execute($commandInput);

        if ($success) {
            static::assertSame(1, $this->countSites());
            static::assertStringContainsString('Site created!', $this->commandTester->getDisplay());
        } else {
            static::assertSame(0, $this->countSites());
            static::assertStringContainsString('Site creation cancelled!', $this->commandTester->getDisplay());
        }
    }

    /**
     * @return iterable<array<string|array<string, mixed>>>
     *
     * @phpstan-return iterable<array{
     *   0: array{
     *     '--no-confirmation'?: bool,
     *     '--name'?: string,
     *     '--relativePath'?: string,
     *     '--host'?: string,
     *     '--enabledFrom'?: string,
     *     '--enabledTo'?: string,
     *     '--locale'?: string,
     *   },
     *   1: array<string>,
     *   2: bool,
     * }>
     */
    public static function provideSiteCreationOptions(): iterable
    {
        yield 'Minimal interactive without confirmation' => [
            [],
            [
                'Site name',
                'localhost',
                '/',
                '-',
                '-',
                '-',
            ],
            false,
        ];

        yield 'Interactive with confirmation' => [
            [],
            [
                'Site name',
                'localhost',
                '/',
                '-',
                '-',
                '-',
                'y',
            ],
            true,
        ];

        yield 'Complete interactive with confirmation' => [
            [],
            [
                'Site name',
                'localhost',
                '/random',
                'now',
                'now',
                'es',
                'y',
            ],
            true,
        ];

        yield 'Minimal non interactive without confirmation' => [
            [
                '--name' => 'Site name',
                '--relativePath' => 'localhost',
                '--host' => '/',
                '--enabledFrom' => '-',
                '--enabledTo' => '-',
                '--locale' => '-',
            ],
            [],
            false,
        ];

        yield 'Minimal non interactive with confirmation' => [
            [
                '--no-confirmation' => true,
                '--name' => 'Site name',
                '--relativePath' => 'localhost',
                '--host' => '/',
                '--enabledFrom' => '-',
                '--enabledTo' => '-',
                '--locale' => '-',
            ],
            [],
            true,
        ];

        yield 'Complete non interactive' => [
            [
                '--no-confirmation' => true,
                '--name' => 'Site name',
                '--relativePath' => 'localhost',
                '--host' => '/random',
                '--enabledFrom' => 'now',
                '--enabledTo' => 'now',
                '--locale' => 'es',
            ],
            [],
            true,
        ];
    }

    /**
     * @psalm-suppress UndefinedPropertyFetch
     */
    private function countSites(): int
    {
        // TODO: Simplify this when dropping support for Symfony 4.
        // @phpstan-ignore-next-line
        $container = method_exists(self::class, 'getContainer') ? static::getContainer() : static::$container;
        $manager = $container->get('doctrine.orm.entity_manager');
        \assert($manager instanceof EntityManagerInterface);

        return $manager->getRepository(SonataPageSite::class)->count([]);
    }
}
