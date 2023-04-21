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
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSite;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSnapshot;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SnapshotAdminTest extends WebTestCase
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
        yield 'List Snapshot' => ['/admin/tests/app/sonatapagesnapshot/list'];
        yield 'Create Snapshot' => ['/admin/tests/app/sonatapagesnapshot/create'];
        yield 'Edit Snapshot' => ['/admin/tests/app/sonatapagesnapshot/1/edit'];
        yield 'Remove Snapshot' => ['/admin/tests/app/sonatapagesnapshot/1/delete'];
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
        yield 'Create Snapshot' => ['/admin/tests/app/sonatapagesnapshot/create', [
            'uniqid' => 'snapshot',
        ], 'btn_create_and_list', [
            'snapshot[page]' => 1,
        ]];

        yield 'Create Snapshot with pageId from parameter' => ['/admin/tests/app/sonatapagesnapshot/create', [
            'pageId' => 1,
        ], 'btn_create_and_list', []];

        yield 'Edit Snapshot' => ['/admin/tests/app/sonatapagesnapshot/1/edit', [
            'uniqid' => 'snapshot',
        ], 'btn_update_and_list', [
            'snapshot[enabled]' => false,
            'snapshot[publicationDateStart]' => 'May 4, 2022, 8:00:00 AM',
            'snapshot[publicationDateEnd]' => 'May 4, 2022, 9:00:00 AM',
        ]];

        yield 'Remove Snapshot' => ['/admin/tests/app/sonatapagesnapshot/1/delete', [], 'btn_delete'];
    }

    /**
     * @psalm-suppress UndefinedPropertyFetch
     */
    public function testCreatedSnapshotsAreEnabled(): void
    {
        $client = self::createClient();

        // TODO: Simplify this when dropping support for Symfony 4.
        // @phpstan-ignore-next-line
        $container = method_exists(self::class, 'getContainer') ? static::getContainer() : static::$container;
        $manager = $container->get('sonata.page.manager.snapshot');
        \assert($manager instanceof SnapshotManagerInterface);

        $this->prepareData();

        static::assertNull($manager->findEnableSnapshot(['pageId' => 1]));

        $client->request('GET', '/admin/tests/app/sonatapagesnapshot/create', [
            'pageId' => 1,
        ]);
        $client->submitForm('btn_create_and_list');
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        static::assertNotNull($manager->findEnableSnapshot(['pageId' => 1]));
    }

    /**
     * @dataProvider provideBatchActionsCases
     */
    public function testBatchActions(string $action): void
    {
        $client = self::createClient();

        $this->prepareData();

        $client->request('GET', '/admin/tests/app/sonatapagesnapshot/list');
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
        yield 'Delete Snapshots' => ['delete'];
        yield 'Toggle Snapshots' => ['toggle_enabled'];
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

        $page = new SonataPagePage();
        $page->setName('name');
        $page->setTemplateCode('default');
        $page->setEnabled(true);
        $page->setSite($site);

        $snapshot = new SonataPageSnapshot();
        $snapshot->setName('name');
        $snapshot->setRouteName('sonata_page_test_route');
        $snapshot->setPage($page);

        $manager->persist($site);
        $manager->persist($page);
        $manager->persist($snapshot);

        $manager->flush();
    }
}
