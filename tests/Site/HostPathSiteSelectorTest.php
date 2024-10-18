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
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Request\SiteRequest;
use Sonata\PageBundle\Site\HostPathSiteSelector as BaseSiteSelector;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Stephen Leavitt <stephen.leavitt@sonyatv.com>
 */
final class HostPathSiteSelectorTest extends TestCase
{
    /**
     * @dataProvider provideSiteCases
     */
    public function testSite(string $expectedName, string $url, string $expectedPath = '/'): void
    {
        [$site, $event] = $this->performHandleKernelRequestTest($url);

        static::assertNotNull($site);
        static::assertSame($expectedName, $site->getName());
        static::assertSame($expectedPath, $event->getRequest()->getPathInfo());
        static::assertSame($site->getLocale(), $event->getRequest()->attributes->get('_locale'));
    }

    /**
     * @return iterable<array{0: string, 1: string, 2?: string}>
     */
    public function provideSiteCases(): iterable
    {
        yield ['Site 0', 'http://www.example.com/test0'];
        yield ['Site 1', 'http://www.example.com/test1'];
        yield ['Site 2', 'http://www.example.com/test2'];
        yield ['Site 3', 'http://www.example.com/test3'];
        yield ['Site 4', 'http://www.example.com/test4'];
        yield ['Site 8', 'http://www.example.com/test'];
        yield ['Site 8', 'http://www.example.com/test/abc', '/abc'];
        yield ['Site 9', 'http://www.example.org/abc', '/abc'];
        yield ['Site 10', 'http://www.example.es/abc', '/abc'];
    }

    /**
     * @dataProvider provideSiteWithRedirectCases
     */
    public function testSiteWithRedirect(string $expectedRedirectUri, string $url, string $path): void
    {
        [$site, $event] = $this->performHandleKernelRequestTest($url);

        static::assertNull($site);

        $response = $event->getResponse();

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame($expectedRedirectUri, $response->getTargetUrl());
        static::assertSame($path, $event->getRequest()->getPathInfo());
        static::assertNull($event->getRequest()->attributes->get('_locale'));
    }

    /**
     * @return iterable<array{string, string, string}>
     */
    public function provideSiteWithRedirectCases(): iterable
    {
        yield ['//www.example.com/test2', 'http://www.example.com/test5', '/test5'];
        yield ['//www.example.com/test2', 'http://www.example.com/test6', '/test6'];
        yield ['//www.example.com/test2', 'http://www.example.com/test7', '/test7'];
        yield ['//www.example.com/test2', 'http://www.example.com', '/'];
    }

    /**
     * @return array{SiteInterface|null, RequestEvent}
     */
    private function performHandleKernelRequestTest(string $url): array
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = SiteRequest::create($url);

        static::assertNull($request->attributes->get('_locale'));

        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $decoratorStrategy = $this->createMock(DecoratorStrategyInterface::class);
        $seoPage = $this->createMock(SeoPageInterface::class);

        $siteSelector = new HostPathSiteSelector($siteManager, $decoratorStrategy, $seoPage);

        // Look for the first site matched that is enabled, has started, and has not expired.
        // localhost is a possible match, but only if no other sites match.
        $siteSelector->handleKernelRequest($event);

        $site = $siteSelector->retrieve();

        return [
            $site,
            $event,
        ];
    }
}

final class HostPathSite extends BaseSite
{
}

final class HostPathSiteSelector extends BaseSiteSelector
{
    public static function _camelize(string $property): string
    {
        $camelized = preg_replace_callback(
            '/(^|[_. ])+(.)/',
            static fn ($match) => ('.' === $match[1] ? '_' : '').strtoupper($match[2]),
            $property
        );
        \assert(null !== $camelized);

        return $camelized;
    }

    /**
     * @return array<SiteInterface>
     */
    protected function getSites(Request $request): array
    {
        return $this->_findSites([
            'host' => [$request->getHost(), 'localhost'],
            'enabled' => true,
        ]);
    }

    /**
     * @param array<string, mixed|array<mixed>> $params
     *
     * @return array<SiteInterface>
     */
    protected function _findSites(array $params): array
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
     * @return array<SiteInterface>
     */
    protected function _getAllSites(): array
    {
        $always = null;
        $now = new \DateTime();
        $one_hour_ago = new \DateTime('-1 hour');
        $one_hour_from_now = new \DateTime('+1 hour');

        /* Create an array to hold enabled sites */
        $sites = [];

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

        /* Site 10 - www.example.es and / as relative path */
        $sites[10] = new HostPathSite();
        $sites[10]->setEnabled(true);
        $sites[10]->setName('Site 10');
        $sites[10]->setRelativePath('/');
        $sites[10]->setHost('www.example.es');
        $sites[10]->setEnabledFrom($always);
        $sites[10]->setEnabledTo($always);
        $sites[10]->setIsDefault(false);
        $sites[10]->setLocale('en_GB');

        return $sites;
    }

    protected function _getFieldValue(object $object, string $fieldName): mixed
    {
        $camelizedFieldName = self::_camelize($fieldName);

        $getters = [];

        $getters[] = 'get'.$camelizedFieldName;
        $getters[] = 'is'.$camelizedFieldName;

        foreach ($getters as $getter) {
            if (method_exists($object, $getter)) {
                return $object->$getter();
            }
        }

        if (isset($object->{$fieldName})) {
            return $object->{$fieldName};
        }

        throw new NoValueException(\sprintf('Unable to retrieve the value of `%s`', $fieldName));
    }
}
