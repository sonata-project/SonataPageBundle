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

namespace Sonata\PageBundle\Request;

use Sonata\PageBundle\Runtime\SonataPagePathRuntime;
use Symfony\Component\HttpFoundation\Request;

final class RequestFactory
{
    /**
     * @var array<string, class-string<Request>>
     */
    private static array $types = [
        'host' => Request::class,
        'host_with_path' => SiteRequest::class,
        'host_with_path_by_locale' => SiteRequest::class,
    ];

    /**
     * @deprecated since sonata-project/page-bundle 4.6.2, to be removed in 5.0.
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $files
     * @param array<string, mixed> $server
     * @param string|resource|null $content
     */
    public static function create(
        string $type,
        string $uri,
        string $method = 'GET',
        array $parameters = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ): Request {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/page-bundle 4.6.2 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            SonataPagePathRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        self::configureFactory($type);

        return \call_user_func_array([self::getClass($type), 'create'], [$uri, $method, $parameters, $cookies, $files, $server, $content]);
    }

    /**
     * @deprecated since sonata-project/page-bundle 4.6.2, to be removed in 5.0.
     */
    public static function createFromGlobals(string $type): Request
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/page-bundle 4.6.2 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            SonataPagePathRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        self::configureFactory($type);

        return \call_user_func_array([self::getClass($type), 'createFromGlobals'], []);
    }

    /**
     * @deprecated since sonata-project/page-bundle 4.6.2, to be removed in 5.0.
     */
    private static function configureFactory(string $type): void
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/page-bundle 4.6.2 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            SonataPagePathRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        if (!\in_array($type, ['host_with_path', 'host_with_path_by_locale'], true)) {
            return;
        }

        Request::setFactory(
            /**
             * @param string|resource|null $content
             */
            static fn (
                array $query = [],
                array $request = [],
                array $attributes = [],
                array $cookies = [],
                array $files = [],
                array $server = [],
                $content = null
            ) => new SiteRequest($query, $request, $attributes, $cookies, $files, $server, $content)
        );
    }

    /**
     * @deprecated since sonata-project/page-bundle 4.6.2, to be removed in 5.0.
     * @return class-string<Request>
     */
    private static function getClass(string $type): string
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/page-bundle 4.6.2 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            SonataPagePathRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        if (!\array_key_exists($type, self::$types)) {
            throw new \RuntimeException('invalid type');
        }

        return self::$types[$type];
    }
}
