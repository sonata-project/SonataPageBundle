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

namespace Sonata\PageBundle\Tests\Admin;

use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Sonata\PageBundle\Admin\PageAdmin;
use Sonata\PageBundle\Controller\PageController;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\Site;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Tests\Model\Page;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PageAdminTest extends TestCase
{
    public function testTabMenuHasLinksWithSubSite(): void
    {
        $request = new Request(['id' => 42]);
        $admin = new PageAdmin(
            $this->createStub(PageManagerInterface::class),
            $this->createStub(SiteManagerInterface::class),
        );
        $admin->setModelManager($this->createStub(ModelManagerInterface::class));
        $admin->setModelClass(Page::class);
        $admin->setBaseControllerName(PageController::class);
        $admin->setCode('admin.page');
        $admin->setMenuFactory(new MenuFactory());
        $admin->setRequest($request);

        $site = $this->createStub(Site::class);
        $site->method('getRelativePath')->willReturn('/my-subsite');

        $page = new Page();
        $page->setRouteName(PageInterface::PAGE_ROUTE_CMS_NAME);
        $page->setUrl('/my-page');
        $page->setSite($site);
        $admin->setSubject($page);

        $routeGenerator = $this->createMock(RouteGeneratorInterface::class);
        $routeGenerator->method('generateMenuUrl')->with(
            $admin,
            static::anything(),
            ['id' => 42],
            UrlGeneratorInterface::ABSOLUTE_PATH
        )->willReturn([
            'route' => 'page_edit',
            'routeParameters' => ['id' => 42],
            'routeAbsolute' => true,
        ]);

        $routeGenerator->expects(static::once())->method('generate')->with(
            'page_slug',
            ['path' => '/my-subsite/my-page']
        );

        $admin->setRouteGenerator($routeGenerator);
        $admin->setSubject($page);

        $admin->getSideMenu('edit');
    }
}
