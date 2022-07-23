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

namespace Sonata\PageBundle\Tests\Page;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\PageServiceManager;
use Sonata\PageBundle\Page\Service\PageServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

final class PageServiceManagerTest extends TestCase
{
    /**
     * @var MockObject&RouterInterface
     */
    protected $router;

    protected PageServiceManager $manager;

    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->manager = new PageServiceManager($this->router);
    }

    /**
     * Test adding a new page service.
     */
    public function testAdd(): void
    {
        $service = $this->createMock(PageServiceInterface::class);

        $this->manager->add('default', $service);

        static::assertSame($service, $this->manager->get('default'));
    }

    /**
     * Test getting a service using a page object.
     *
     * @depends testAdd
     */
    public function testGetByPage(): void
    {
        $service = $this->createMock(PageServiceInterface::class);
        $this->manager->add('my-type', $service);

        $page = $this->createMock(PageInterface::class);
        $page->expects(static::once())->method('getType')->willReturn('my-type');

        static::assertSame(
            $service,
            $this->manager->get($page),
            'Should return the page service'
        );
    }

    /**
     * Test getting all page services.
     *
     * @depends testAdd
     */
    public function testGetAll(): void
    {
        $this->manager->add('service1', $service1 = $this->createMock(PageServiceInterface::class));
        $this->manager->add('service2', $service2 = $this->createMock(PageServiceInterface::class));

        static::assertSame(
            ['service1' => $service1, 'service2' => $service2],
            $this->manager->getAll(),
            'Should return all page services'
        );
    }

    /**
     * @depends testAdd
     */
    public function testDefault(): void
    {
        $default = $this->createMock(PageServiceInterface::class);
        $this->manager->setDefault($default);

        static::assertSame(
            $default,
            $this->manager->get('non-existing'),
            'Should return the default page service'
        );
    }

    /**
     * Test the page execution through a service.
     *
     * @depends testDefault
     */
    public function testExecute(): void
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);

        $page = $this->createMock(PageInterface::class);
        $page->expects(static::once())->method('getType')->willReturn('my-type');

        $service = $this->createMock(PageServiceInterface::class);
        $service->expects(static::once())
            ->method('execute')
            ->with(static::equalTo($page), static::equalTo($request))
            ->willReturn($response);
        $this->manager->add('my-type', $service);

        static::assertSame(
            $response,
            $this->manager->execute($page, $request),
            'Should return a response'
        );
    }
}
