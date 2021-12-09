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

final class BasePageServiceTest extends TestCase
{
    public function testName(): void
    {
        $service = new ConcretePageService('my name');

        static::assertSame('my name', $service->getName());
    }

    public function testExecution(): void
    {
        $service = new ConcretePageService('my name');
        $page = $this->createMock(PageInterface::class);
        $request = $this->createMock(Request::class);

        static::assertInstanceOf(
            Response::class,
            $service->execute($page, $request),
            'Should return a Response object'
        );
    }
}

/**
 * Concrete page service implementation for test purposes; Should only implement the execute method.
 */
final class ConcretePageService extends BasePageService
{
    public function execute(PageInterface $page, Request $request, array $parameters = [], ?Response $response = null)
    {
        // do nothing
        $response = new Response('ok');

        return $response;
    }
}
