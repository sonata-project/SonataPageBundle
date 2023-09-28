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
use Sonata\PageBundle\Tests\App\Entity\SonataPageBlock;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
        yield 'List Shared Block' => ['/admin/tests/app/sonatapageblock/shared/list'];
        yield 'List Shared Block types' => ['/admin/tests/app/sonatapageblock/shared/create'];

        yield 'Create Shared Block' => ['/admin/tests/app/sonatapageblock/shared/create', [
            'type' => 'sonata.page.block.shared_block',
        ]];

        yield 'Edit Shared Block' => ['/admin/tests/app/sonatapageblock/shared/1/edit'];
        yield 'Remove Shared Block' => ['/admin/tests/app/sonatapageblock/shared/1/delete'];
    }

    /**
     * @dataProvider provideFormsUrlsCases
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
    public static function provideFormsUrlsCases(): iterable
    {
        // Blocks From SonataBlockBundle
        yield 'Create Shared Block - Text' => ['/admin/tests/app/sonatapageblock/shared/create', [
            'uniqid' => 'shared_block',
            'type' => 'sonata.block.service.text',
        ], 'btn_create_and_edit', [
            'shared_block[name]' => 'Name',
            'shared_block[enabled]' => 1,
            'shared_block[settings][content]' => 'Text',
        ]];

        yield 'Create Shared Block - Template' => ['/admin/tests/app/sonatapageblock/shared/create', [
            'uniqid' => 'shared_block',
            'type' => 'sonata.block.service.template',
        ], 'btn_create_and_edit', [
            'shared_block[name]' => 'Name',
            'shared_block[enabled]' => 1,
            'shared_block[settings][template]' => '@SonataBlock/Block/block_template.html.twig',
        ]];

        yield 'Create Shared Block - Menu' => ['/admin/tests/app/sonatapageblock/shared/create', [
            'uniqid' => 'shared_block',
            'type' => 'sonata.block.service.menu',
        ], 'btn_create_and_edit', [
            'shared_block[name]' => 'Name',
            'shared_block[enabled]' => 1,
            'shared_block[settings][title]' => 'Title',
            'shared_block[settings][safe_labels]' => 1,
            'shared_block[settings][current_class]' => 'active',
            'shared_block[settings][first_class]' => 'first',
            'shared_block[settings][last_class]' => 'last',
            'shared_block[settings][menu_template]' => '@SonataBlock/Block/block_core_menu.html.twig',
        ]];

        yield 'Create Shared Block - RSS' => ['/admin/tests/app/sonatapageblock/shared/create', [
            'uniqid' => 'shared_block',
            'type' => 'sonata.block.service.rss',
        ], 'btn_create_and_edit', [
            'shared_block[name]' => 'Name',
            'shared_block[enabled]' => 1,
            'shared_block[settings][url]' => 'https://sonata-project.org',
            'shared_block[settings][title]' => 'Title',
            'shared_block[settings][translation_domain]' => 'SonataPageBundle',
            'shared_block[settings][icon]' => 'fa fa-home',
            'shared_block[settings][class]' => 'custom_class',
        ]];

        // Blocks From SonataPageBundle
        yield 'Create Shared Block - Page Container' => ['/admin/tests/app/sonatapageblock/shared/create', [
            'uniqid' => 'shared_block',
            'type' => 'sonata.page.block.container',
        ], 'btn_create_and_edit', [
            'shared_block[name]' => 'Name',
            'shared_block[enabled]' => 1,
            'shared_block[settings][code]' => 'code',
            'shared_block[settings][layout]' => '{{ CONTENT }}',
            'shared_block[settings][class]' => 'custom_class',
            'shared_block[settings][template]' => '@SonataPage/Block/block_container.html.twig',
        ]];

        yield 'Create Shared Block - Breadcrumb' => ['/admin/tests/app/sonatapageblock/shared/create', [
            'uniqid' => 'shared_block',
            'type' => 'sonata.page.block.breadcrumb',
        ], 'btn_create_and_edit', [
            'shared_block[name]' => 'Name',
            'shared_block[enabled]' => 1,
            'shared_block[settings][title]' => 'Title',
            'shared_block[settings][safe_labels]' => 1,
            'shared_block[settings][current_class]' => 'current',
            'shared_block[settings][first_class]' => 'first',
            'shared_block[settings][last_class]' => 'last',
            'shared_block[settings][menu_template]' => 'default',
        ]];

        yield 'Create Shared Block - Shared Block' => ['/admin/tests/app/sonatapageblock/shared/create', [
            'uniqid' => 'shared_block',
            'type' => 'sonata.page.block.shared_block',
        ], 'btn_create_and_edit', [
            'shared_block[name]' => 'Name',
            'shared_block[enabled]' => 1,
            'shared_block[settings][blockId]' => 1,
        ]];

        yield 'Create Shared Block - Page List' => ['/admin/tests/app/sonatapageblock/shared/create', [
            'uniqid' => 'shared_block',
            'type' => 'sonata.page.block.pagelist',
        ], 'btn_create_and_edit', [
            'shared_block[name]' => 'Name',
            'shared_block[enabled]' => 1,
            'shared_block[settings][title]' => 'Title',
            'shared_block[settings][translation_domain]' => 'SonataPageBundle',
            'shared_block[settings][icon]' => 'fa fa-home',
            'shared_block[settings][class]' => 'custom_class',
            'shared_block[settings][mode]' => 'form.choice_public',
        ]];

        yield 'Edit Shared Block' => ['/admin/tests/app/sonatapageblock/shared/1/edit', [], 'btn_update_and_list', []];
        yield 'Remove Shared Block' => ['/admin/tests/app/sonatapageblock/shared/1/delete', [], 'btn_delete'];
    }

    /**
     * @dataProvider provideBatchActionsCases
     */
    public function testBatchActions(string $action): void
    {
        $client = self::createClient();

        $this->prepareData();

        $client->request('GET', '/admin/tests/app/sonatapageblock/shared/list');
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
        yield 'Delete Shared Blocks' => ['delete'];
    }

    private function prepareData(): void
    {
        $manager = self::getContainer()->get('doctrine.orm.entity_manager');
        \assert($manager instanceof EntityManagerInterface);

        $block = new SonataPageBlock();
        $block->setType('sonata.block.service.text');

        $manager->persist($block);

        $manager->flush();
    }
}
