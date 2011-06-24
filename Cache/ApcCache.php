<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Cache;

use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ApcCache implements CacheInterface
{
    protected $servers;

    protected $prefix;

    protected $collection;

    protected $router;

    public function __construct($router, $token, $prefix, array $servers)
    {
        $this->token   = $token;
        $this->prefix  = $prefix;
        $this->servers = $servers;
        $this->router  = $router;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function flushAll()
    {
        $result = true;
        foreach ($this->servers as $server) {
            if (count(explode('.', $server['ip']) == 3)) {
                $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            } else {
                $socket = socket_create(AF_INET6, SOCK_STREAM, SOL_TCP);
            }

            // generate the raw http request
            $command = sprintf("GET %s HTTP/1.1\r\n", $this->router->generate('sonata_page_apc_cache', array('token' => $this->token)));
            $command .= sprintf("Host: %s\r\n", $server['domain']);
            $command .= "Connection: Close\r\n\r\n";

            // setup the default timeout (avoid max execution time)
            socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 2, 'usec' => 0));
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 2, 'usec' => 0));

            socket_connect($socket, $server['ip'], $server['port']);

            socket_write($socket, $command);

            $content = socket_read($socket, 1024);

            if ($result) {
              $result = substr($content, -2) == 'ok' ? true : false;
            }
        }

        return $result;
    }

    public function flush(array $keys = array())
    {
        return $this->flushAll();
    }

    public function has(CacheElement $cacheElement)
    {
        return apc_exists($this->computeCacheKeys($cacheElement));
    }

    /**
     * @param CacheElement $cacheElement
     * @return mixed
     */
    public function set(CacheElement $cacheElement)
    {
        $return = apc_store(
            $this->computeCacheKeys($cacheElement),
            $cacheElement->getValue(),
            $cacheElement->getTtl()
        );

        return $return;
    }

    public function computeCacheKeys(CacheElement $cacheElement)
    {
        $keys = $cacheElement->getKeys();

        ksort($keys);

        return md5($this->prefix.serialize($keys));
    }

    /**
     * @param CacheElement $cacheElement
     * @return mixed
     */
    public function get(CacheElement $cacheElement)
    {
        return apc_fetch($this->computeCacheKeys($cacheElement));
    }

    public function cacheAction($token)
    {
        if ($this->getToken() == $token) {
            apc_clear_cache('user');

            return new Response('ok', 200, array(
                'Cache-Control' => 'no-cache, must-revalidate'
            ));
        }

        throw new AccessDeniedException('invalid token');
    }
}