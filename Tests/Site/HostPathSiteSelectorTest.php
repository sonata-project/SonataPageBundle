<?php

/**
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Site;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;

use Sonata\PageBundle\Request\SiteRequest;
use Sonata\PageBundle\Entity\BaseSite;
use Sonata\PageBundle\Site\HostPathSiteSelector as BaseSiteSelector;

/**
 * @author Stephen Leavitt <stephen.leavitt@sonyatv.com>
 */
class HostPathSiteSelectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Perform the actual handleKernelSiteRequest method test
     */
    protected function performHandleKernelRequestTest($url)
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = SiteRequest::create($url);

        // Ensure request locale is null
        $this->assertNull($request->attributes->get('_locale'));

        $event = new GetResponseEvent($kernel, $request, 'master');

        $siteManager = $this->getMock('Sonata\PageBundle\Model\SiteManagerInterface');
        $decoratorStrategy = $this->getMock('Sonata\PageBundle\CmsManager\DecoratorStrategyInterface');
        $seoPage = $this->getMock('Sonata\SeoBundle\Seo\SeoPageInterface');

        $siteSelector = new HostPathSiteSelector($siteManager, $decoratorStrategy, $seoPage);

        // Look for the first site matched that is enabled, has started, and has not expired.
        // localhost is a possible match, but only if no other sites match.
        $siteSelector->handleKernelRequest($event);

        $site = $siteSelector->retrieve();

        return array(
            $site,
            $event
        );
    }

    /**
     * Site Test #1 - Should match "Site 0"
     */
    public function testSite1()
    {
        // Retrieve the site that would be matched from the request
        list($site, $event) = $this->performHandleKernelRequestTest('http://www.example.com/test0');

        // Ensure we retrieved the "Site 0" site.
        $this->assertEquals('Site 0', $site->getName());

        // Ensure request path info is /
        $this->assertEquals('/', $event->getRequest()->getPathInfo());

        // Ensure request locale matches site locale
        $this->assertEquals($site->getLocale(), $event->getRequest()->attributes->get('_locale'));
    }

    /**
     * Site Test #2 - Should match "Site 1"
     */
    public function testSite2()
    {
        // Retrieve the site that would be matched from the request
        list($site, $event) = $this->performHandleKernelRequestTest('http://www.example.com/test1');

        // Ensure we retrieved the "Site 1" site.
        $this->assertEquals('Site 1', $site->getName());

        // Ensure request path info is /
        $this->assertEquals('/', $event->getRequest()->getPathInfo());

        // Ensure request locale matches site locale
        $this->assertEquals($site->getLocale(), $event->getRequest()->attributes->get('_locale'));
    }

    /**
     * Site Test #3 - Should match "Site 2"
     */
    public function testSite3()
    {
        // Retrieve the site that would be matched from the request
        list($site, $event) = $this->performHandleKernelRequestTest('http://www.example.com/test2');

        // Ensure we retrieved the "Site 1" site.
        $this->assertEquals('Site 2', $site->getName());

        // Ensure request path info is /
        $this->assertEquals('/', $event->getRequest()->getPathInfo());

        // Ensure request locale matches site locale
        $this->assertEquals($site->getLocale(), $event->getRequest()->attributes->get('_locale'));
    }

    /**
     * Site Test #4 - Should match "Site 3"
     */
    public function testSite4()
    {
        // Retrieve the site that would be matched from the request
        list($site, $event) = $this->performHandleKernelRequestTest('http://www.example.com/test3');

        // Ensure we retrieved the "Site 1" site.
        $this->assertEquals('Site 3', $site->getName());

        // Ensure request path info is /
        $this->assertEquals('/', $event->getRequest()->getPathInfo());

        // Ensure request locale matches site locale
        $this->assertEquals($site->getLocale(), $event->getRequest()->attributes->get('_locale'));
    }

    /**
     * Site Test #5 - Should match "Site 4"
     */
    public function testSite5()
    {
        // Retrieve the site that would be matched from the request
        list($site, $event) = $this->performHandleKernelRequestTest('http://www.example.com/test4');

        // Ensure we retrieved the "Site 1" site.
        $this->assertEquals('Site 4', $site->getName());

        // Ensure request path info is /
        $this->assertEquals('/', $event->getRequest()->getPathInfo());

        // Ensure request locale matches site locale
        $this->assertEquals($site->getLocale(), $event->getRequest()->attributes->get('_locale'));
    }

    /**
     * Site Test #6 - Should match no site, and the event response should yield a RedirectResponse object to redirect to "Site 2"
     */
    public function testSite6()
    {
        // Retrieve the site that would be matched from the request
        list($site, $event) = $this->performHandleKernelRequestTest('http://www.example.com/test5');

        // Ensure no site was retrieved
        $this->assertNull($site);

        // Retrieve the event's response object
        $response = $event->getResponse();

        // Ensure the response was a redirect to the default site
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);

        // Ensure the redirect url is for "Site 2"
        $this->assertEquals('http://www.example.com/test2', $response->getTargetUrl());

        // Ensure request path info is /test5
        $this->assertEquals('/test5', $event->getRequest()->getPathInfo());

        // Ensure request locale is null
        $this->assertNull($event->getRequest()->attributes->get('_locale'));
    }

    /**
     * Site Test #7 - Should match no site, and the event response should yield a RedirectResponse object to redirect to "Site 2"
     */
    public function testSite7()
    {
        // Retrieve the site that would be matched from the request
        list($site, $event) = $this->performHandleKernelRequestTest('http://www.example.com/test6');

        // Ensure no site was retrieved
        $this->assertNull($site);

        // Retrieve the event's response object
        $response = $event->getResponse();

        // Ensure the response was a redirect to the default site
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);

        // Ensure the redirect url is for "Site 2"
        $this->assertEquals('http://www.example.com/test2', $response->getTargetUrl());

        // Ensure request path info is /test6
        $this->assertEquals('/test6', $event->getRequest()->getPathInfo());

        // Ensure request locale is null
        $this->assertNull($event->getRequest()->attributes->get('_locale'));
    }

    /**
     * Site Test #8 - Should match no site, and the event response should yield a RedirectResponse object to redirect to "Site 2"
     */
    public function testSite8()
    {
        // Retrieve the site that would be matched from the request
        list($site, $event) = $this->performHandleKernelRequestTest('http://www.example.com/test7');

        // Ensure no site was retrieved
        $this->assertNull($site);

        // Retrieve the event's response object
        $response = $event->getResponse();

        // Ensure the response was a redirect to the default site
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);

        // Ensure the redirect url is for "Site 2"
        $this->assertEquals('http://www.example.com/test2', $response->getTargetUrl());

        // Ensure request path info is /test7
        $this->assertEquals('/test7', $event->getRequest()->getPathInfo());

        // Ensure request locale is null
        $this->assertNull($event->getRequest()->attributes->get('_locale'));
    }

    /**
     * Site Test #9 - Should match no site, and the event response should yield a RedirectResponse object to redirect to "Site 2"
     */
    public function testSite9()
    {
        // Retrieve the site that would be matched from the request
        list($site, $event) = $this->performHandleKernelRequestTest('http://www.example.com');

        // Ensure no site was retrieved
        $this->assertNull($site);

        // Retrieve the event's response object
        $response = $event->getResponse();

        // Ensure the response was a redirect to the default site
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);

        // Ensure the redirect url is for "Site 2"
        $this->assertEquals('http://www.example.com/test2', $response->getTargetUrl());

        // Ensure request path info is /
        $this->assertEquals('/', $event->getRequest()->getPathInfo());

        // Ensure request locale is null
        $this->assertNull($event->getRequest()->attributes->get('_locale'));
    }

    /**
     * Site Test #10 - Should match "Site 8"
     */
    public function testSite10()
    {
        // Retrieve the site that would be matched from the request
        list($site, $event) = $this->performHandleKernelRequestTest('http://www.example.com/test');

        // Ensure we retrieved the correct site.
        $this->assertEquals('Site 8', $site->getName());

        // Ensure request path info is /
        $this->assertEquals('/', $event->getRequest()->getPathInfo());

        // Ensure request locale matches site locale
        $this->assertEquals($site->getLocale(), $event->getRequest()->attributes->get('_locale'));
    }

    /**
     * Site Test #11 - Should match "Site 8" and path info should match "/abc"
     */
    public function testSite11()
    {
        // Retrieve the site that would be matched from the request
        list($site, $event) = $this->performHandleKernelRequestTest('http://www.example.com/test/abc');

        // Ensure we retrieved the correct site.
        $this->assertEquals('Site 8', $site->getName());

        // Ensure request path info is /abc
        $this->assertEquals('/abc', $event->getRequest()->getPathInfo());

        // Ensure request locale matches site locale
        $this->assertEquals($site->getLocale(), $event->getRequest()->attributes->get('_locale'));
    }

    /**
     * Site Test #12 - Should match "Site 9" and path info should match "/abc"
     */
    public function testSite12()
    {
        // Retrieve the site that would be matched from the request
        list($site, $event) = $this->performHandleKernelRequestTest('http://www.example.org/abc');

        // Ensure we retrieved the correct site.
        $this->assertEquals('Site 9', $site->getName());

        // Ensure request path info is /abc
        $this->assertEquals('/abc', $event->getRequest()->getPathInfo());

        // Ensure request locale matches site locale
        $this->assertEquals($site->getLocale(), $event->getRequest()->attributes->get('_locale'));
    }

}

class HostPathSite extends BaseSite
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }
}

class HostPathSiteSelector extends BaseSiteSelector
{
    /**
     * @return array
     */
    protected function getSites(Request $request)
    {
        return $this->_findSites(array(
            'host'    => array($request->getHost(), 'localhost'),
            'enabled' => true,
        ));
    }

    /**
     * @param array $params
     *
     * @return array
     */
    protected function _findSites(array $params)
    {
        $all_sites = $this->_getAllSites();

        $matched_sites = array();

        foreach ($all_sites as $site) {
            $valid_site = true;

            foreach ($params as $param_name => $param_value) {
                $value = $this->_getFieldValue($site, $param_name);

                if (is_array($param_value)) {
                    if (!in_array($value, $param_value)) {
                        $valid_site = false;
                    }
                } else {
                    if ($value !== $param_value) {
                        $valid_site = false;
                    }
                }
            }

            if ($valid_site) {
                $matched_sites[] = $site;
            }
        }

        return $matched_sites;
    }

    /**
     * @return array
     */
    protected function _getAllSites()
    {
        $always = null;
        $now = new \DateTime();
        $one_hour_ago = new \DateTime('-1 hour');
        $one_hour_from_now = new \DateTime('+1 hour');

        /* Create an array to hold enabled sites */
        $sites = array();

        /* Site 0 - Always valid */
        $sites[0] = new HostPathSite();
        $sites[0]->setEnabled(true);
        $sites[0]->setName('Site 0');
        $sites[0]->setRelativePath('/test0');
        $sites[0]->setHost('localhost');
        $sites[0]->setEnabledFrom($always);
        $sites[0]->setEnabledTo($always);
        $sites[0]->setIsDefault(false);
        $sites[0]->setLocale('en_US');

        /* Site 1 - Always valid */
        $sites[1] = new HostPathSite();
        $sites[1]->setEnabled(true);
        $sites[1]->setName('Site 1');
        $sites[1]->setRelativePath('/test1');
        $sites[1]->setHost('www.example.com');
        $sites[1]->setEnabledFrom($always);
        $sites[1]->setEnabledTo($always);
        $sites[1]->setIsDefault(false);
        $sites[1]->setLocale('en_US');

        /* Site 2 - Valid from one hour ago until one hour from now */
        $sites[2] = new HostPathSite();
        $sites[2]->setEnabled(true);
        $sites[2]->setName('Site 2');
        $sites[2]->setRelativePath('/test2');
        $sites[2]->setHost('www.example.com');
        $sites[2]->setEnabledFrom($one_hour_ago);
        $sites[2]->setEnabledTo($one_hour_from_now);
        $sites[2]->setIsDefault(true);
        $sites[2]->setLocale('en_US');

        /* Site 3 - Valid from one hour ago */
        $sites[3] = new HostPathSite();
        $sites[3]->setEnabled(true);
        $sites[3]->setName('Site 3');
        $sites[3]->setRelativePath('/test3');
        $sites[3]->setHost('www.example.com');
        $sites[3]->setEnabledFrom($one_hour_ago);
        $sites[3]->setEnabledTo($always);
        $sites[3]->setIsDefault(false);
        $sites[3]->setLocale('en_US');

        /* Site 4 - Valid until one hour from now */
        $sites[4] = new HostPathSite();
        $sites[4]->setEnabled(true);
        $sites[4]->setName('Site 4');
        $sites[4]->setRelativePath('/test4');
        $sites[4]->setHost('www.example.com');
        $sites[4]->setEnabledFrom($always);
        $sites[4]->setEnabledTo($one_hour_from_now);
        $sites[4]->setIsDefault(false);
        $sites[4]->setLocale('en_US');

        /* Site 5 - Valid from one hour from now */
        $sites[5] = new HostPathSite();
        $sites[5]->setEnabled(true);
        $sites[5]->setName('Site 5');
        $sites[5]->setRelativePath('/test5');
        $sites[5]->setHost('www.example.com');
        $sites[5]->setEnabledFrom($one_hour_from_now);
        $sites[5]->setEnabledTo($always);
        $sites[5]->setIsDefault(false);
        $sites[5]->setLocale('en_US');

        /* Site 6 - Valid until one hour ago */
        $sites[6] = new HostPathSite();
        $sites[6]->setEnabled(true);
        $sites[6]->setName('Site 6');
        $sites[6]->setRelativePath('/test6');
        $sites[6]->setHost('www.example.com');
        $sites[6]->setEnabledFrom($always);
        $sites[6]->setEnabledTo($one_hour_ago);
        $sites[6]->setIsDefault(false);
        $sites[6]->setLocale('en_US');

        /* Site 7 - Site is disabled */
        $sites[7] = new HostPathSite();
        $sites[7]->setEnabled(false);
        $sites[7]->setName('Site 7');
        $sites[7]->setRelativePath('/test7');
        $sites[7]->setHost('www.example.com');
        $sites[7]->setEnabledFrom($always);
        $sites[7]->setEnabledTo($always);
        $sites[7]->setLocale('en_US');

        /* Site 8 - Relative path is a substring of the relative path of the other sites */
        $sites[8] = new HostPathSite();
        $sites[8]->setEnabled(true);
        $sites[8]->setName('Site 8');
        $sites[8]->setRelativePath('/test');
        $sites[8]->setHost('www.example.com');
        $sites[8]->setEnabledFrom($always);
        $sites[8]->setEnabledTo($always);
        $sites[8]->setIsDefault(false);
        $sites[8]->setLocale('en_GB');

        /* Site 9 - www.example.org and no relative path */
        $sites[9] = new HostPathSite();
        $sites[9]->setEnabled(true);
        $sites[9]->setName('Site 9');
        $sites[9]->setRelativePath(null);
        $sites[9]->setHost('www.example.org');
        $sites[9]->setEnabledFrom($always);
        $sites[9]->setEnabledTo($always);
        $sites[9]->setIsDefault(false);
        $sites[9]->setLocale('en_GB');

        return $sites;
    }

    /**
     *
     * @param object $object
     * @param string $fieldName
     *
     * @return mixed
     */
    protected function _getFieldValue($object, $fieldName)
    {
        $camelizedFieldName = self::_camelize($fieldName);

        $getters = array();

        $getters[] = 'get' . $camelizedFieldName;
        $getters[] = 'is' . $camelizedFieldName;

        foreach ($getters as $getter) {
            if (method_exists($object, $getter)) {
                return call_user_func(array($object, $getter));
            }
        }

        if (isset($object->{$fieldName})) {
            return $object->{$fieldName};
        }

        throw new NoValueException(sprintf('Unable to retrieve the value of `%s`', $this->getName()));
    }

    /**
     * Camelize a string
     *
     * @static
     *
     * @param string $property
     *
     * @return string
     */
    public static function _camelize($property)
    {
        return preg_replace_callback('/(^|[_. ])+(.)/', function ($match) {
            return ('.' === $match[1] ? '_' : '') . strtoupper($match[2]);
        }, $property);
    }
}
