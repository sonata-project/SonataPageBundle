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
use Sonata\PageBundle\Tests\App\Entity\SonataPageSnapshot;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

final class PageAdminTest extends WebTestCase
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
        yield 'Tree Page' => ['/admin/tests/app/sonatapagepage/tree'];

        yield 'List Page' => ['/admin/tests/app/sonatapagepage/list', ['filter' => [
            'name' => ['value' => 'name'],
        ]]];

        yield 'Create Page' => ['/admin/tests/app/sonatapagepage/create'];
        yield 'Edit Page' => ['/admin/tests/app/sonatapagepage/1/edit'];
        yield 'Show Page' => ['/admin/tests/app/sonatapagepage/1/show'];
        yield 'Remove Page' => ['/admin/tests/app/sonatapagepage/1/delete'];
        yield 'Compose Page' => ['/admin/tests/app/sonatapagepage/1/compose'];
        yield 'Compose Show Page' => ['/admin/tests/app/sonatapagepage/compose/container/1'];

        // Snapshot child pages
        yield 'List Snapshot Page' => ['/admin/tests/app/sonatapagepage/1/sonatapagesnapshot/list'];
        yield 'Create Snapshot Page' => ['/admin/tests/app/sonatapagepage/1/sonatapagesnapshot/create'];
        yield 'Edit Snapshot Page' => ['/admin/tests/app/sonatapagepage/1/sonatapagesnapshot/1/edit'];
        yield 'Remove Snapshot Page' => ['/admin/tests/app/sonatapagepage/1/sonatapagesnapshot/1/delete'];

        // Block child pages
        yield 'List Block' => ['/admin/tests/app/sonatapagepage/1/sonatapageblock/list'];
        yield 'Create Block' => ['/admin/tests/app/sonatapagepage/1/sonatapageblock/create'];
        yield 'Edit Block' => ['/admin/tests/app/sonatapagepage/1/sonatapageblock/1/edit'];
        yield 'Remove Block' => ['/admin/tests/app/sonatapagepage/1/sonatapageblock/1/delete'];
        yield 'Compose preview Block' => ['/admin/tests/app/sonatapagepage/1/sonatapageblock/2/compose-preview'];
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

        $snapshot = new SonataPageSnapshot();
        $snapshot->setName('name');
        $snapshot->setRouteName('sonata_page_test_route');
        $snapshot->setPage($page);

        $parentBlock = new SonataPageBlock();
        $parentBlock->setType('sonata.page.block.container');
        $parentBlock->setPage($page);

        $block = new SonataPageBlock();
        $block->setType('sonata.block.service.text');
        $block->setParent($parentBlock);

        $manager->persist($page);
        $manager->persist($snapshot);
        $manager->persist($parentBlock);
        $manager->persist($block);

        $manager->flush();
    }
}
