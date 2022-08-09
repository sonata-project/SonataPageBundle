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

final class PageServiceManagerTest extends TestCase
{
    private PageServiceManager $manager;

    protected function setUp(): void
    {
        $this->manager = new PageServiceManager();
    }

    public function testAdd(): void
    {
        $service = $this->createMock(PageServiceInterface::class);

        $this->manager->add('default', $service);

        static::assertSame($service, $this->manager->get('default'));
    }

    /**
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
