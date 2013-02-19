<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Page\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\Service\BasePageService;

/**
 * Test the abstract base page service
 */
class BasePageServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test the service name
     */
    public function testName()
    {
        // GIVEN
        $service = new ConcretePageService('my name');

        // WHEN
        $name = $service->getName();

        // THEN
        $this->assertEquals('my name', $name);
    }

    /**
     * Test the service execution
     */
    public function testExecution()
    {
        // GIVEN
        $service = new ConcretePageService('my name');
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        // WHEN
        $response = $service->execute($page, $request);

        // THEN
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response, 'Should return a Response object');
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
    public function execute(PageInterface $page, Request $request, array $parameters = array(), Response $response = null)
    {
        // do nothing
        $response = new Response('ok');

        return $response;
    }
}
