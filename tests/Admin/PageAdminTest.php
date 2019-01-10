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
use Prophecy\Argument;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Sonata\PageBundle\Admin\PageAdmin;
use Sonata\PageBundle\Controller\PageController;
use Sonata\PageBundle\Model\Site;
use Sonata\PageBundle\Tests\Model\Page;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PageAdminTest extends TestCase
{
    public function testTabMenuHasLinksWithSubSite()
    {
        $request = new Request(['id' => 42]);
        $admin = new PageAdmin(
            'admin.page',
            Page::class,
            PageController::class
        );
        $admin->setMenuFactory(new MenuFactory());
        $admin->setRequest($request);

        $site = $this->prophesize(Site::class);
        $site->getRelativePath()->willReturn('/my-subsite');

        $page = $this->prophesize(Page::class);
        $page->getRouteName()->willReturn(Page::PAGE_ROUTE_CMS_NAME);
        $page->getUrl()->willReturn('/my-page');
        $page->isHybrid()->willReturn(false);
        $page->isInternal()->willReturn(false);
        $page->getSite()->willReturn($site->reveal());
        $admin->setSubject($page->reveal());

        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeGenerator->generateMenuUrl(
            $admin,
            Argument::any(),
            ['id' => 42],
            UrlGeneratorInterface::ABSOLUTE_PATH
        )->willReturn([
            'route' => 'page_edit',
            'routeParameters' => ['id' => 42],
            'routeAbsolute' => true,
        ]);

        $routeGenerator->generate(
            'page_slug',
            ['path' => '/my-subsite/my-page']
        )->shouldBeCalled();

        $admin->setRouteGenerator($routeGenerator->reveal());
        $admin->setSubject($page->reveal());

        $admin->buildTabMenu('edit');
    }
}
