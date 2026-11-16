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

namespace Sonata\PageBundle\Tests\Route;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Route\RoutePartitioner;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class RoutePartitionerTest extends TestCase
{
    public function testPartitionWithLocalizedRoutes(): void
    {
        $collection = new RouteCollection();
        $collection->add('app_home.en', new Route('/en/home'));
        $collection->add('app_home.fi', new Route('/home'));
        $collection->add('app_about', new Route('/about'));

        $partitioner = new RoutePartitioner();
        $partitioner->partition($collection);

        $enCollection = $partitioner->getLocaleCollection('en');
        $fiCollection = $partitioner->getLocaleCollection('fi');
        $neutralCollection = $partitioner->getNeutralCollection();

        static::assertNotNull($enCollection);
        static::assertNotNull($fiCollection);

        // English collection should have app_home.en + neutral routes
        static::assertNotNull($enCollection->get('app_home.en'));
        static::assertNotNull($enCollection->get('app_about'));
        static::assertNull($enCollection->get('app_home.fi'));

        // Finnish collection should have app_home.fi + neutral routes
        static::assertNotNull($fiCollection->get('app_home.fi'));
        static::assertNotNull($fiCollection->get('app_about'));
        static::assertNull($fiCollection->get('app_home.en'));

        // Neutral collection should only have non-localized routes
        static::assertNotNull($neutralCollection->get('app_about'));
        static::assertNull($neutralCollection->get('app_home.en'));
        static::assertNull($neutralCollection->get('app_home.fi'));
    }

    public function testPartitionWithAllowedLocales(): void
    {
        $collection = new RouteCollection();
        $collection->add('app_home.en', new Route('/en/home'));
        $collection->add('app_home.fi', new Route('/home'));
        $collection->add('admin.dashboard', new Route('/admin/dashboard')); // Should be neutral

        $partitioner = new RoutePartitioner();
        $partitioner->setAllowedLocales(['en', 'fi']);
        $partitioner->partition($collection);

        // admin.dashboard should be treated as neutral (dashboard is not a valid locale)
        $neutralCollection = $partitioner->getNeutralCollection();
        static::assertNotNull($neutralCollection->get('admin.dashboard'));

        // Should have en and fi locales
        static::assertTrue($partitioner->hasLocale('en'));
        static::assertTrue($partitioner->hasLocale('fi'));
        static::assertFalse($partitioner->hasLocale('dashboard'));
    }

    public function testGetAlias(): void
    {
        $collection = new RouteCollection();
        $collection->add('app_home.en', new Route('/en/home'));
        $collection->add('app_home.fi', new Route('/home'));

        $partitioner = new RoutePartitioner();
        $partitioner->partition($collection);

        static::assertSame('app_home.en', $partitioner->getAlias('app_home', 'en'));
        static::assertSame('app_home.fi', $partitioner->getAlias('app_home', 'fi'));
        static::assertNull($partitioner->getAlias('app_home', 'sv'));
        static::assertNull($partitioner->getAlias('nonexistent', 'en'));
    }

    public function testHasBase(): void
    {
        $collection = new RouteCollection();
        $collection->add('app_home.en', new Route('/en/home'));
        $collection->add('app_about', new Route('/about'));

        $partitioner = new RoutePartitioner();
        $partitioner->partition($collection);

        static::assertTrue($partitioner->hasBase('app_home'));
        static::assertFalse($partitioner->hasBase('app_about')); // No localized variants
        static::assertFalse($partitioner->hasBase('nonexistent'));
    }

    public function testGetLocales(): void
    {
        $collection = new RouteCollection();
        $collection->add('app_home.en', new Route('/en/home'));
        $collection->add('app_home.fi', new Route('/home'));
        $collection->add('app_about.sv', new Route('/sv/about'));

        $partitioner = new RoutePartitioner();
        $partitioner->partition($collection);

        $locales = $partitioner->getLocales();

        static::assertContains('en', $locales);
        static::assertContains('fi', $locales);
        static::assertContains('sv', $locales);
        static::assertCount(3, $locales);
    }

    public function testPartitionIdempotent(): void
    {
        $collection = new RouteCollection();
        $collection->add('app_home.en', new Route('/en/home'));

        $partitioner = new RoutePartitioner();
        $partitioner->partition($collection);
        $partitioner->partition($collection); // Second call should be no-op

        static::assertTrue($partitioner->hasLocale('en'));
        static::assertNotNull($partitioner->getLocaleCollection('en'));
    }

    public function testRoutesWithoutDotAreNeutral(): void
    {
        $collection = new RouteCollection();
        $collection->add('app_home', new Route('/home'));
        $collection->add('api_users', new Route('/api/users'));

        $partitioner = new RoutePartitioner();
        $partitioner->partition($collection);

        $neutralCollection = $partitioner->getNeutralCollection();

        static::assertNotNull($neutralCollection->get('app_home'));
        static::assertNotNull($neutralCollection->get('api_users'));
        static::assertCount(0, $partitioner->getLocales());
    }

    public function testMalformedSuffixesAreTreatedAsNeutral(): void
    {
        $collection = new RouteCollection();
        $collection->add('app_home.', new Route('/home')); // Empty suffix
        $collection->add('app_about./test', new Route('/about')); // Suffix with slash

        $partitioner = new RoutePartitioner();
        $partitioner->partition($collection);

        $neutralCollection = $partitioner->getNeutralCollection();

        static::assertNotNull($neutralCollection->get('app_home.'));
        static::assertNotNull($neutralCollection->get('app_about./test'));
    }

    public function testDebugSummary(): void
    {
        $collection = new RouteCollection();
        $collection->add('app_home.en', new Route('/en/home'));
        $collection->add('app_home.fi', new Route('/home'));
        $collection->add('app_about', new Route('/about'));

        $partitioner = new RoutePartitioner();
        $partitioner->partition($collection);

        $summary = $partitioner->debugSummary();

        static::assertArrayHasKey('locales', $summary);
        static::assertArrayHasKey('neutral_count', $summary);
        static::assertArrayHasKey('localized_counts', $summary);
        static::assertArrayHasKey('bases', $summary);

        static::assertCount(2, $summary['locales']); // en, fi
        static::assertSame(1, $summary['neutral_count']); // app_about
        static::assertSame(1, $summary['bases']); // app_home
    }

    public function testSetAllowedLocalesAfterPartitionIsIgnored(): void
    {
        $collection = new RouteCollection();
        $collection->add('app_home.en', new Route('/en/home'));
        $collection->add('app_home.fi', new Route('/home'));

        $partitioner = new RoutePartitioner();
        $partitioner->partition($collection);

        // This should be silently ignored
        $partitioner->setAllowedLocales(['en']);

        // Both locales should still be present
        static::assertTrue($partitioner->hasLocale('en'));
        static::assertTrue($partitioner->hasLocale('fi'));
    }

    public function testEmptyCollection(): void
    {
        $collection = new RouteCollection();

        $partitioner = new RoutePartitioner();
        $partitioner->partition($collection);

        static::assertCount(0, $partitioner->getLocales());
        static::assertCount(0, $partitioner->getNeutralCollection());
    }
}
