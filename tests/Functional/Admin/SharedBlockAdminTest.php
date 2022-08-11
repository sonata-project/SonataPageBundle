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

namespace Sonata\PageBundle\Tests\Functional\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\PageBundle\Tests\App\AppKernel;
use Sonata\PageBundle\Tests\App\Entity\SonataPageBlock;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

final class SharedBlockAdminTest extends WebTestCase
{
    /**
     * @dataProvider provideCrudUrlsCases
     *
     * @param array<string, mixed> $parameters
     */
    public function testCrudUrls(string $url, array $parameters = []): void
    {
        $client = self::createClient();

        $this->prepareData();

        $client->request('GET', $url, $parameters);

        self::assertResponseIsSuccessful();
    }

    /**
     * @return iterable<array<string|array<string, mixed>>>
     *
     * @phpstan-return iterable<array{0: string, 1?: array<string, mixed>}>
     */
    public static function provideCrudUrlsCases(): iterable
    {
        yield 'List Block' => ['/admin/tests/app/sonatapageblock/shared/list'];
        yield 'List Block types' => ['/admin/tests/app/sonatapageblock/shared/create'];

        yield 'Create Block' => ['/admin/tests/app/sonatapageblock/shared/create', [
            'type' => 'sonata.page.block.shared_block',
        ]];

        yield 'Edit Block' => ['/admin/tests/app/sonatapageblock/shared/1/edit'];
        yield 'Remove Block' => ['/admin/tests/app/sonatapageblock/shared/1/delete'];
    }

    /**
     * @dataProvider provideFormUrlsCases
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $fieldValues
     */
    public function testFormsUrls(string $url, array $parameters, string $button, array $fieldValues = []): void
    {
        $client = self::createClient();

        $this->prepareData();

        $client->request('GET', $url, $parameters);
        $client->submitForm($button, $fieldValues);
        $client->followRedirect();

        self::assertResponseIsSuccessful();
    }

    /**
     * @return iterable<array<string|array<string, mixed>>>
     *
     * @phpstan-return iterable<array{0: string, 1: array<string, mixed>, 2: string, 3?: array<string, mixed>}>
     */
    public static function provideFormUrlsCases(): iterable
    {
        yield 'Create Shared Block - Shared Block' => ['/admin/tests/app/sonatapageblock/shared/create', [
            'uniqid' => 'shared_block',
            'type' => 'sonata.page.block.shared_block',
        ], 'btn_create_and_list', [
            'shared_block[name]' => 'Name',
            'shared_block[settings][blockId]' => 1,
        ]];

        yield 'Edit Shared Block' => ['/admin/tests/app/sonatapageblock/shared/1/edit', [], 'btn_update_and_list', []];
        yield 'Remove Shared Block' => ['/admin/tests/app/sonatapageblock/shared/1/delete', [], 'btn_delete'];
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

        $block = new SonataPageBlock();
        $block->setType('sonata.block.service.text');

        $manager->persist($block);

        $manager->flush();
    }
}
