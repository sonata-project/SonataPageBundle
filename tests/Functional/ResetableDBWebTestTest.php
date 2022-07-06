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

use Sonata\PageBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;

abstract class ResetableDBWebTestTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $application = new Application($this->client->getKernel());
        $application->setAutoExit(false);

        $application->run(new ArrayInput([
            'command' => 'doctrine:database:create',
        ]));

        $application->run(new ArrayInput([
            'command' => 'doctrine:schema:create',
        ]));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $application = new Application($this->client->getKernel());
        $application->setAutoExit(false);
        $application->run(new ArrayInput([
            'command' => 'doctrine:database:drop',
            '--force' => '1',
        ]));
    }

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }
}
