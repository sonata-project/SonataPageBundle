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

namespace Sonata\PageBundle\Tests\Page\Service;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\Service\BasePageService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test the abstract base page service.
 */
class BasePageServiceTest extends TestCase
{
    /**
     * test the service name.
     */
    public function testName(): void
    {
        // GIVEN
        $service = new ConcretePageService('my name');

        // WHEN
        $name = $service->getName();

        // THEN
        $this->assertEquals('my name', $name);
    }

    /**
     * Test the service execution.
     */
    public function testExecution(): void
    {
        // GIVEN
        $service = new ConcretePageService('my name');
        $page = $this->createMock(PageInterface::class);
        $request = $this->createMock(Request::class);

        // WHEN
        $response = $service->execute($page, $request);

        // THEN
        $this->assertInstanceOf(Response::class, $response, 'Should return a Response object');
    }
}

/**
 * Concrete page service implementation for test purposes; Should only implement the execute method.
 */
class ConcretePageService extends BasePageService
{
    /**
     * {@inheritdoc}
     */
    public function execute(PageInterface $page, Request $request, array $parameters = [], Response $response = null)
    {
        // do nothing
        $response = new Response('ok');

        return $response;
    }
}
