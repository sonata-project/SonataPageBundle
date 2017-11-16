<?php

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
use Sonata\PageBundle\Admin\PageAdmin;
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
            'Sonata\PageBundle\Model\Page',
            'Sonata\PageBundle\Controller\PageController'
        );
        $admin->setMenuFactory(new MenuFactory());
        $admin->setRequest($request);

        $site = $this->prophesize('Sonata\PageBundle\Model\Site');
        $site->getRelativePath()->willReturn('/my-subsite');

        $page = $this->prophesize('Sonata\PageBundle\Model\Page');
        $page->getRouteName()->willReturn(Page::PAGE_ROUTE_CMS_NAME);
        $page->getUrl()->willReturn('/my-page');
        $page->isHybrid()->willReturn(false);
        $page->isInternal()->willReturn(false);
        $page->getSite()->willReturn($site->reveal());
        $admin->setSubject($page->reveal());

        $routeGenerator = $this->prophesize('Sonata\AdminBundle\Route\RouteGeneratorInterface');
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
