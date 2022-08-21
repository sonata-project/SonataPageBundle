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
use Sonata\PageBundle\Tests\App\Entity\SonataPageSite;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SiteAdminTest extends WebTestCase
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
        yield 'List Site' => ['/admin/tests/app/sonatapagesite/list'];
        yield 'Create Site' => ['/admin/tests/app/sonatapagesite/create'];
        yield 'Edit Site' => ['/admin/tests/app/sonatapagesite/1/edit'];
        yield 'Show Page' => ['/admin/tests/app/sonatapagesite/1/show'];
        yield 'Remove Site' => ['/admin/tests/app/sonatapagesite/1/delete'];
        yield 'Create Snaphosts Site' => ['/admin/tests/app/sonatapagesite/1/snapshots'];
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
        yield 'Create Site' => ['/admin/tests/app/sonatapagesite/create', [
            'uniqid' => 'site',
        ], 'btn_create_and_list', [
            'site[name]' => 'Name',
            'site[host]' => 'localhost',
        ]];

        yield 'Edit Site' => ['/admin/tests/app/sonatapagesite/1/edit', [], 'btn_update_and_list', []];
        yield 'Remove Site' => ['/admin/tests/app/sonatapagesite/1/delete', [], 'btn_delete'];
        yield 'Create Snaphosts Site' => ['/admin/tests/app/sonatapagesite/1/snapshots', [], 'create'];
    }

    /**
     * @dataProvider provideBatchActionsCases
     */
    public function testBatchActions(string $action): void
    {
        $client = self::createClient();

        $this->prepareData();

        $client->request('GET', '/admin/tests/app/sonatapagesite/list');
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
        yield 'Delete Sites' => ['delete'];
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

        $manager->persist($site);

        $manager->flush();
    }
}
