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
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

final class BlockAdminTest extends WebTestCase
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
        yield 'List Block' => ['/admin/tests/app/sonatapageblock/create'];
        yield 'List Block types' => ['/admin/tests/app/sonatapageblock/create'];

        yield 'Create Block' => ['/admin/tests/app/sonatapageblock/create', [
            'type' => 'sonata.page.block.shared_block',
        ]];

        yield 'Edit Block' => ['/admin/tests/app/sonatapageblock/1/edit'];
        yield 'Remove Block' => ['/admin/tests/app/sonatapageblock/1/delete'];
        yield 'Compose preview Block' => ['/admin/tests/app/sonatapageblock/3/compose-preview'];
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
        yield 'Create Block - Text' => ['/admin/tests/app/sonatapageblock/create', [
            'uniqid' => 'block',
            'type' => 'sonata.block.service.text',
        ], 'btn_create_and_list', []];

        yield 'Create Block compose mode - Text' => ['/admin/tests/app/sonatapageblock/create', [
            'composer' => '1',
            'uniqid' => 'block',
            'type' => 'sonata.block.service.text',
        ], 'btn_create_and_list', []];

        yield 'Edit Block' => ['/admin/tests/app/sonatapageblock/1/edit', [], 'btn_update_and_list', []];
        yield 'Remove Block' => ['/admin/tests/app/sonatapageblock/1/delete', [], 'btn_delete'];
    }

    /**
     * @dataProvider provideSwitchParentCases
     *
     * @param array{block_id?: int|string|null, parent_id?: int|string|null} $parameters
     */
    public function testSwitchParentForBlock(array $parameters, bool $success): void
    {
        $client = self::createClient();

        $this->prepareData();

        $client->request('GET', '/admin/tests/app/sonatapageblock/switch-parent', $parameters);

        if ($success) {
            self::assertResponseIsSuccessful();
        } else {
            self::assertResponseStatusCodeSame(400);
        }
    }

    /**
     * @return iterable<array<string|array<string, mixed>>>
     *
     * @phpstan-return iterable<array{0: array{block_id?: int|string|null, parent_id?: int|string|null}, 1: bool}>
     */
    public static function provideSwitchParentCases(): iterable
    {
        yield 'Missing all parameters' => [[], false];

        yield 'Missing parent_id' => [[
            'block_id' => 3,
        ], false];

        yield 'Unable to find block' => [[
            'block_id' => 'foo',
            'parent_id' => 2,
        ], false];

        yield 'Unable to find parent block' => [[
            'block_id' => 3,
            'parent_id' => 'foo',
        ], false];

        yield 'Switch parent Block' => [[
            'block_id' => 3,
            'parent_id' => 2,
        ], true];
    }

    /**
     * @dataProvider provideDispositionCases
     *
     * @param array{disposition?: array<array{id: int|string, position: int|numeric-string}>} $parameters
     */
    public function testSavePositionForBlock(array $parameters, bool $success): void
    {
        $client = self::createClient();

        $this->prepareData();

        $client->request(
            'POST',
            '/admin/tests/app/sonatapageblock/save-position',
            $parameters,
            [],
            ['HTTP_Content-type' => 'application/json']
        );

        if ($success) {
            self::assertResponseIsSuccessful();
        } else {
            self::assertResponseStatusCodeSame(400);
        }
    }

    /**
     * @return iterable<array<bool|array<string, mixed>>>
     *
     * @phpstan-return iterable<array{0: array{disposition?: array<array{id: int|string, position: int|numeric-string}>}, 1: bool}>
     */
    public static function provideDispositionCases(): iterable
    {
        yield 'Missing disposition' => [[], false];

        yield 'Empty disposition' => [[
            'disposition' => [],
        ], false];

        yield 'Update block disposition' => [[
            'disposition' => [
                ['id' => 1, 'position' => 1],
                ['id' => 2, 'position' => '2'],
            ],
        ], true];
    }

    /**
     * @dataProvider provideBatchActionsCases
     */
    public function testBatchActions(string $action): void
    {
        $client = self::createClient();

        $this->prepareData();

        $client->request('GET', '/admin/tests/app/sonatapageblock/list');
        $client->submitForm('OK', [
            'all_elements' => true,
            'action' => $action,
        ]);
        $client->submitForm('Yes, execute');
        $client->followRedirect();

        self::assertResponseIsSuccessful();
    }

    /**
     * @return iterable<array<string>>
     *
     * @phpstan-return iterable<array{0: string}>
     */
    public static function provideBatchActionsCases(): iterable
    {
        yield 'Delete Blocks' => ['delete'];
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

        $page = new SonataPagePage();
        $page->setName('name');
        $page->setTemplateCode('default');

        $parentBlock = new SonataPageBlock();
        $parentBlock->setType('sonata.page.block.container');
        $parentBlock->setPage($page);

        $parentBlock2 = new SonataPageBlock();
        $parentBlock2->setType('sonata.page.block.container');
        $parentBlock2->setPage($page);

        $block = new SonataPageBlock();
        $block->setType('sonata.block.service.text');
        $block->setParent($parentBlock);

        $manager->persist($page);
        $manager->persist($parentBlock);
        $manager->persist($parentBlock2);
        $manager->persist($block);

        $manager->flush();
    }
}
