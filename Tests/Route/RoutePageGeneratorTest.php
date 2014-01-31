<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Route;

use Sonata\PageBundle\CmsManager\DecoratorStrategy;
use Sonata\PageBundle\Tests\Model\Page;
use Sonata\PageBundle\Route\RoutePageGenerator;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Test RoutePageGenerator service
 *
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
class RoutePageGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RoutePageGenerator
     */
    protected $routePageGenerator;

    /**
     * Set up dependencies
     */
    public function setUp()
    {
        $this->routePageGenerator = $this->getRoutePageGenerator();
    }

    /**
     * Tests site update route method with
     */
    public function testUpdateRoutes()
    {
        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $tmpFile = tmpfile();

        $this->routePageGenerator->update($site, new StreamOutput($tmpFile));

        fseek($tmpFile, 0);

        $output = '';

        while (!feof($tmpFile)) {
            $output = fread($tmpFile, 4096);
        }

        $this->assertRegExp('/CREATE(.*)route1(.*)\/first_custom_route/', $output);
        $this->assertRegExp('/CREATE(.*)route1(.*)\/first_custom_route/', $output);
        $this->assertRegExp('/CREATE(.*)404/', $output);
        $this->assertRegExp('/CREATE(.*)500/', $output);

        $this->assertRegExp('/ERROR(.*)test_hybrid_page_not_exists/', $output);
    }

    /**
     * Returns a mock of Symfony router
     *
     * @return \Symfony\Component\Routing\Router
     */
    protected function getRouterMock()
    {
        $collection = new RouteCollection();
        $collection->add('route1', new Route('first_custom_route'));
        $collection->add('route2', new Route('second_custom_route'));

        $router = $this->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();

        $router->expects($this->any())->method('getRouteCollection')->will($this->returnValue($collection));

        return $router;
    }

    /**
     * Returns Sonata route page generator service
     *
     * @return RoutePageGenerator
     */
    protected function getRoutePageGenerator()
    {
        $router = $this->getRouterMock();

        $pageManager = $this->getMockBuilder('Sonata\PageBundle\Entity\PageManager')
            ->disableOriginalConstructor()
            ->getMock();

        $pageManager->expects($this->any())->method('create')->will($this->returnValue(new Page()));

        $hybridPage= new Page();
        $hybridPage->setRouteName('test_hybrid_page_not_exists');

        $pageManager->expects($this->any())->method('getHybridPages')->will($this->returnValue(array($hybridPage)));

        $decoratorStrategy = new DecoratorStrategy(array(), array(), array());

        $exceptionListener = $this->getMockBuilder('Sonata\PageBundle\Listener\ExceptionListener')
            ->disableOriginalConstructor()
            ->getMock();

        $exceptionListener->expects($this->any())->method('getHttpErrorCodes')->will($this->returnValue(array(404, 500)));

        return new RoutePageGenerator($router, $pageManager, $decoratorStrategy, $exceptionListener);
    }
}
