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

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Panther\PantherTestCase;

abstract class BasePantherTestCase extends PantherTestCase
{
    public static function setUpBeforeClass(): void
    {
        StaticDriver::setKeepStaticConnections(false);

        static::prepareDatabase();
    }

    public static function tearDownAfterClass(): void
    {
        self::rollbackDatabase();

        StaticDriver::setKeepStaticConnections(true);
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
        \assert($manager instanceof EntityManagerInterface);

        $meta = $manager->getMetadataFactory()->getAllMetadata();

        $tool = new SchemaTool($manager);
        $tool->dropSchema($meta);
        $tool->createSchema($meta);

        self::ensureKernelShutdown();
    }
}
