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

namespace Sonata\PageBundle\Tests\Site;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Entity\BaseSite;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Site\HostSiteSelector as BaseSiteSelector;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Stephen Leavitt <stephen.leavitt@sonyatv.com>
 */
class HostSiteSelectorTest extends TestCase
{
    /**
     * @dataProvider siteProvider
     */
    public function testSite(string $expectedName, string $url)
    {
        // Retrieve the site that would be matched from the request
        list($site, $event) = $this->performHandleKernelRequestTest($url);

        // Ensure we retrieved the correct site.
        $this->assertSame($expectedName, $site->getName());
    }

    public function siteProvider(): \Generator
    {
        yield ['Site 0', 'http://localhost'];
        yield ['Site 1', 'http://www.example1.com'];
        yield ['Site 2', 'http://www.example2.com'];
        yield ['Site 3', 'http://www.example3.com'];
        yield ['Site 4', 'http://www.example4.com'];
        yield ['Site 0', 'http://www.example5.com'];
        yield ['Site 0', 'http://www.example6.com'];
        yield ['Site 0', 'http://www.example7.com'];
    }

    /**
     * Perform the actual handleKernelRequest method test.
     */
    protected function performHandleKernelRequestTest($url): array
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = Request::create($url);

        // Ensure request locale is null
        $this->assertNull($request->attributes->get('_locale'));

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $decoratorStrategy = $this->createMock(DecoratorStrategyInterface::class);
        $seoPage = $this->createMock(SeoPageInterface::class);

        $siteSelector = new HostSiteSelector($siteManager, $decoratorStrategy, $seoPage);

        // Look for the first site matched that is enabled, has started, and has not expired.
        // localhost is a possible match, but only if no other sites match.
        $siteSelector->handleKernelRequest($event);

        $site = $siteSelector->retrieve();

        // Ensure request locale matches site locale
        $this->assertSame($site->getLocale(), $request->attributes->get('_locale'));

        return [
            $site,
            $event,
        ];
    }
}

class HostSite extends BaseSite
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }
}

class HostSiteSelector extends BaseSiteSelector
{
    /**
     * Camelize a string.
     *
     * @static
     *
     * @param string $property
     *
     * @return string
     */
    public static function _camelize($property)
    {
        return preg_replace_callback('/(^|[_. ])+(.)/', static function ($match) {
            return ('.' === $match[1] ? '_' : '').strtoupper($match[2]);
        }, $property);
    }

    /**
     * @return array
     */
    protected function getSites(Request $request)
    {
        return $this->_findSites(
            [
                'host' => [$request->getHost(), 'localhost'],
                'enabled' => true,
            ]
        );
    }

    /**
     * @param array $params
     *
     * @return array
     */
    protected function _findSites($params)
    {
        $all_sites = $this->_getAllSites();

        $matched_sites = [];

        foreach ($all_sites as $site) {
            $valid_site = true;

            foreach ($params as $param_name => $param_value) {
                $value = $this->_getFieldValue($site, $param_name);

                if (\is_array($param_value)) {
                    if (!\in_array($value, $param_value, true)) {
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
        $sites = [];

        /* Site 0 - Always valid */
        $sites[0] = new HostSite();
        $sites[0]->setEnabled(true);
        $sites[0]->setName('Site 0');
        $sites[0]->setRelativePath('/');
        $sites[0]->setHost('localhost');
        $sites[0]->setEnabledFrom($always);
        $sites[0]->setEnabledTo($always);
        $sites[0]->setLocale('en_US');

        /* Site 1 - Always valid */
        $sites[1] = new HostSite();
        $sites[1]->setEnabled(true);
        $sites[1]->setName('Site 1');
        $sites[1]->setRelativePath('/');
        $sites[1]->setHost('www.example1.com');
        $sites[1]->setEnabledFrom($always);
        $sites[1]->setEnabledTo($always);
        $sites[1]->setLocale('en_US');

        /* Site 2 - Valid from one hour ago until one hour from now */
        $sites[2] = new HostSite();
        $sites[2]->setEnabled(true);
        $sites[2]->setName('Site 2');
        $sites[2]->setRelativePath('/');
        $sites[2]->setHost('www.example2.com');
        $sites[2]->setEnabledFrom($one_hour_ago);
        $sites[2]->setEnabledTo($one_hour_from_now);
        $sites[2]->setLocale('en_US');

        /* Site 3 - Valid from one hour ago */
        $sites[3] = new HostSite();
        $sites[3]->setEnabled(true);
        $sites[3]->setName('Site 3');
        $sites[3]->setRelativePath('/');
        $sites[3]->setHost('www.example3.com');
        $sites[3]->setEnabledFrom($one_hour_ago);
        $sites[3]->setEnabledTo($always);
        $sites[3]->setLocale('en_US');

        /* Site 4 - Valid until one hour from now */
        $sites[4] = new HostSite();
        $sites[4]->setEnabled(true);
        $sites[4]->setName('Site 4');
        $sites[4]->setRelativePath('/');
        $sites[4]->setHost('www.example4.com');
        $sites[4]->setEnabledFrom($always);
        $sites[4]->setEnabledTo($one_hour_from_now);
        $sites[4]->setLocale('en_US');

        /* Site 5 - Valid from one hour from now */
        $sites[5] = new HostSite();
        $sites[5]->setEnabled(true);
        $sites[5]->setName('Site 5');
        $sites[5]->setRelativePath('/');
        $sites[5]->setHost('www.example5.com');
        $sites[5]->setEnabledFrom($one_hour_from_now);
        $sites[5]->setEnabledTo($always);
        $sites[5]->setLocale('en_US');

        /* Site 6 - Valid until one hour ago */
        $sites[6] = new HostSite();
        $sites[6]->setEnabled(true);
        $sites[6]->setName('Site 6');
        $sites[6]->setRelativePath('/');
        $sites[6]->setHost('www.example6.com');
        $sites[6]->setEnabledFrom($always);
        $sites[6]->setEnabledTo($one_hour_ago);
        $sites[6]->setLocale('en_US');

        /* Site 7 - Site is disabled */
        $sites[7] = new HostSite();
        $sites[7]->setEnabled(false);
        $sites[7]->setName('Site 7');
        $sites[7]->setRelativePath('/');
        $sites[7]->setHost('www.example7.com');
        $sites[7]->setEnabledFrom($always);
        $sites[7]->setEnabledTo($always);
        $sites[7]->setLocale('en_US');

        return $sites;
    }

    /**
     * @param object $object
     * @param string $fieldName
     */
    protected function _getFieldValue($object, $fieldName)
    {
        $camelizedFieldName = self::_camelize($fieldName);

        $getters = [];

        $getters[] = 'get'.$camelizedFieldName;
        $getters[] = 'is'.$camelizedFieldName;

        foreach ($getters as $getter) {
            if (method_exists($object, $getter)) {
                return \call_user_func([$object, $getter]);
            }
        }

        if (isset($object->{$fieldName})) {
            return $object->{$fieldName};
        }

        throw new NoValueException(sprintf('Unable to retrieve the value of `%s`', $this->getName()));
    }
}
