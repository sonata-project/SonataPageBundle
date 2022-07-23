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

namespace Sonata\PageBundle\Tests\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Request\AdminFetcher;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\PageBundle\Controller\PageAdminController;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Service\Contract\CreateSnapshotByPageInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class PageAdminControllerTest extends TestCase
{
    private Container $container;

    /**
     * @var MockObject&AdminInterface
     */
    private $admin;

    private Request $request;

    private PageAdminController $controller;

    /**
     * @var MockObject&Environment
     */
    private $twig;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->admin = $this->createMock(AdminInterface::class);
        $this->request = new Request();
        $this->twig = $this->createMock(Environment::class);

        $this->container->set('twig', $this->twig);

        $this->configureCRUDController();

        $this->controller = new PageAdminController();
        $this->controller->setContainer($this->container);
        $this->controller->configureAdmin($this->request);
    }

    public function testCreateSnapshotByPage(): void
    {
        $adminSnapshot = $this->createMock(AbstractAdmin::class);
        $this->container->set('sonata.page.admin.snapshot', $adminSnapshot);

        $this->admin->method('generateUrl')->willReturn('https://fake.bar');

        $createSnapshotByPageMock = $this->createMock(CreateSnapshotByPageInterface::class);
        $createSnapshotByPageMock
            ->expects(static::once())
            ->method('createByPage');

        $this->container->set('sonata.page.service.create_snapshot', $createSnapshotByPageMock);

        $pageMock = $this->createMock(PageInterface::class);
        $queryMock = $this->createMock(ProxyQueryInterface::class);
        $queryMock
            ->method('execute')
            ->willReturn([$pageMock]);

        //Run code
        $this->controller->batchActionSnapshot($queryMock);
    }

    private function configureCRUDController(): void
    {
        $pool = new Pool($this->container, ['admin_code' => 'admin_code']);

        $adminFetcher = new AdminFetcher($pool);

        $templateRegistry = $this->createStub(TemplateRegistryInterface::class);

        $this->configureGetCurrentRequest($this->request);

        $this->request->query->set('_sonata_admin', 'admin_code');

        $this->container->set('admin_code', $this->admin);
        $this->container->set('sonata.admin.pool.do-not-use', $pool);
        $this->container->set('admin_code.template_registry', $templateRegistry);
        $this->container->set('sonata.admin.request.fetcher', $adminFetcher);

        $this->admin->method('getCode')->willReturn('admin_code');
    }

    private function configureGetCurrentRequest(Request $request): void
    {
        $requestStack = $this->createStub(RequestStack::class);

        $this->container->set('request_stack', $requestStack);
        $requestStack->method('getCurrentRequest')->willReturn($request);
    }
}
