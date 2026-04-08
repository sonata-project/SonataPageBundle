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

namespace Sonata\PageBundle\Tests\Functional;

use DAMA\DoctrineTestBundle\PHPUnit\SkipDatabaseRollback;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Panther\PantherTestCase;

#[SkipDatabaseRollback]
abstract class BasePantherTestCase extends PantherTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::prepareDatabase();
    }

    protected function tearDown(): void
    {
        self::rollbackDatabase();
        parent::tearDown();
    }

    public function testCrudUrls(): void
    {
        $client = self::createPantherClient();
        $client->request('GET', '/admin/tests/app/sonatapagesite/list');

        static::assertTrue(true);
    }

    abstract protected static function prepareDatabase(): void;

    private static function rollbackDatabase(): void
    {
        $manager = self::getContainer()->get('doctrine.orm.entity_manager');
        static::assertInstanceOf(EntityManagerInterface::class, $manager);

        $meta = $manager->getMetadataFactory()->getAllMetadata();

        $tool = new SchemaTool($manager);
        $tool->dropSchema($meta);
        $tool->createSchema($meta);

        self::ensureKernelShutdown();
    }
}
