<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

class RequestFactory
{
    private static $types = array(
        'host'                     => 'Symfony\Component\HttpFoundation\Request',
        'host_with_path'           => 'Sonata\PageBundle\Request\SiteRequest',
        'host_with_path_by_locale' => 'Sonata\PageBundle\Request\SiteRequest',
    );

    /**
     * @param string $type
     */
    private static function configureFactory($type)
    {
        if (version_compare(Kernel::VERSION, '2.5', '<')) {
            // nothing to configure as Request::setFactory require SF > 2.5
            return;
        }

        if (!in_array($type, array('host_with_path', 'host_with_path_by_locale'))) {
            return;
        }

        Request::setFactory(function (
            array $query = array(),
            array $request = array(),
            array $attributes = array(),
            array $cookies = array(),
            array $files = array(),
            array $server = array(),
            $content = null
        ) {
            return new SiteRequest($query, $request, $attributes, $cookies, $files, $server, $content);
        });
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private static function getClass($type)
    {
        if (!array_key_exists($type, self::$types)) {
            throw new \RuntimeException('invalid type');
        }

        return self::$types[$type];
    }

    /**
     * @param string $type
     * @param string $uri
     * @param string $method
     * @param array  $parameters
     * @param array  $cookies
     * @param array  $files
     * @param array  $server
     * @param null   $content
     *
     * @return Request|SiteRequest
     */
    public static function create($type, $uri, $method = 'GET', $parameters = array(), $cookies = array(), $files = array(), $server = array(), $content = null)
    {
        self::configureFactory($type);

        return call_user_func_array(array(self::getClass($type), 'create'), array($uri, $method, $parameters, $cookies, $files, $server, $content));
    }

    /**
     * @param string $type
     *
     * @return Request|SiteRequest
     */
    public static function createFromGlobals($type)
    {
        self::configureFactory($type);

        return call_user_func_array(array(self::getClass($type), 'createFromGlobals'), array());
    }
}