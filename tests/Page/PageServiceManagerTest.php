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

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\PageServiceManager;
use Sonata\PageBundle\Page\Service\PageServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Test the page service manager.
 */
class PageServiceManagerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $router;

    /**
     * @var PageServiceManager
     */
    protected $manager;

    /**
     * setup each test.
     */
    public function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->manager = new PageServiceManager($this->router);
    }

    /**
     * Test adding a new page service.
     */
    public function testAdd(): void
    {
        // GIVEN
        $service = $this->createMock(PageServiceInterface::class);

        // WHEN
        $this->manager->add('default', $service);

        // THEN
        $this->assertEquals($service, $this->manager->get('default'));
    }

    /**
     * Test getting a service using a page object.
     *
     * @depends testAdd
     */
    public function testGetByPage(): void
    {
        // GIVEN
        $service = $this->createMock(PageServiceInterface::class);
        $this->manager->add('my-type', $service);

        $page = $this->createMock(PageInterface::class);
        $page->expects($this->once())->method('getType')->will($this->returnValue('my-type'));

        // WHEN
        $pageService = $this->manager->get($page);

        // THEN
        $this->assertSame($service, $pageService, 'Should return the page service');
    }

    /**
     * Test getting all page services.
     *
     * @depends testAdd
     */
    public function testGetAll(): void
    {
        // GIVEN
        $service1 = $this->createMock(PageServiceInterface::class);
        $service2 = $this->createMock(PageServiceInterface::class);
        $this->manager->add('service1', $service1);
        $this->manager->add('service2', $service2);

        // WHEN
        $services = $this->manager->getAll();

        // THEN
        $this->assertEquals(['service1' => $service1, 'service2' => $service2], $services, 'Should return all page services');
    }

    /**
     * Test the default page service.
     *
     * @depends testAdd
     */
    public function testDefault(): void
    {
        // GIVEN
        $default = $this->createMock(PageServiceInterface::class);
        $this->manager->setDefault($default);

        // WHEN
        $service = $this->manager->get('non-existing');

        // THEN
        $this->assertEquals($default, $service, 'Should return the default page service');
    }

    /**
     * Test the page execution through a service.
     *
     * @depends testDefault
     */
    public function testExecute(): void
    {
        // GIVEN
        $request = $this
            ->getMockBuilder(Request::class)
            ->disableOriginalClone()
            ->getMock();
        $response = $this->createMock(Response::class);

        $page = $this->createMock(PageInterface::class);
        $page->expects($this->once())->method('getType')->will($this->returnValue('my-type'));

        $service = $this->createMock(PageServiceInterface::class);
        $service->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($page), $this->equalTo($request))
            ->will($this->returnValue($response));
        $this->manager->add('my-type', $service);

        // WHEN
        $result = $this->manager->execute($page, $request);

        // THEN
        $this->assertSame($response, $result, 'Should return a response');
    }
}
