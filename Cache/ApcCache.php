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
use Symfony\Component\Routing\RouterInterface;

class ApcCache implements CacheInterface
{
    protected $servers;

    protected $prefix;

    protected $collection;

    protected $router;

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param string $token
     * @param string $prefix
     * @param array $servers
     */
    public function __construct(RouterInterface $router, $token, $prefix, array $servers)
    {
        $this->token   = $token;
        $this->prefix  = $prefix;
        $this->servers = $servers;
        $this->router  = $router;
    }

    /**
     * @return string
     */
    private function getToken()
    {
        return $this->token;
    }

    /**
     * @return bool
     */
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

    /**
     * @param array $keys
     * @return bool
     */
    public function flush(array $keys = array())
    {
        return $this->flushAll();
    }

    /**
     * @param CacheElement $cacheElement
     * @return bool|\string[]
     */
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

    /**
     * @param CacheElement $cacheElement
     * @return string
     */
    private function computeCacheKeys(CacheElement $cacheElement)
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

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @param $token
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * @return bool
     */
    public function isContextual()
    {
        return false;
    }
}