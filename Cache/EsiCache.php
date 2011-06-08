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
use Symfony\Component\Process\Process;


/**
 * http://www.varnish-cache.org/docs/2.1/reference/varnishadm.html
 *  echo vcl.use foo | varnishadm -T localhost:999 -S /var/db/secret
 *  echo vcl.use foo | ssh vhost varnishadm -T localhost:999 -S /var/db/secret
 *
 *  in the config.yml file :
 *     echo %s "%s" | varnishadm -T localhost:999 -S /var/db/secret
 *     echo %s "%s" | ssh vhost varnishadm -T localhost:999 -S /var/db/secret
 */
class EsiCache implements CacheInterface
{
    protected $router;

    public function __construct(array $servers = array(), Router $router)
    {
        $this->servers = $servers;
        $this->router  = $router;
    }

    public function flushAll()
    {
        return $this->runCommand('purge', 'req.url ~ .*');
    }

    /**
     * @param string $command
     * @param string $expression
     * @return bool
     */
    private function runCommand($command, $expression)
    {
        $return = true;
        foreach($this->servers as $server) {
            $command = str_replace(array('{{ COMMAND }}', '{{ EXPRESSION }}'), array($command, $expression), $server);
            $process = new Process($command);

            if ($process->run() == 0) {
                continue;
            }

            $return = false;
        }

        return $return;
    }

    public function flush(array $keys = array())
    {
        $parameters = array();
        foreach($keys as $key => $value) {
            $key = strtr(strtolower($key), '_', '-');
            $parameters[] = sprintf('obj.http.x-sonata-%s == %s', $key, $value);
        }

        $purge = implode(" && ", $parameters);

        $this->runCommand('purge', $purge);
    }

    public function has(CacheElement $cacheElement)
    {
        return true;
    }

    public function get(CacheElement $cacheElement)
    {
        $content = sprintf('<esi:include src="%s" />',
            $this->getUrl($cacheElement),
            $cacheElement->getTtl()
        );
        
        return new Response($content, 200, $headers);
    }

    public function createResponse(CacheElement $cacheElement)
    {
        return new Response;
    }

    public function set(CacheElement $cacheElement)
    {
        // todo : prefetch the url ?
    }

    public function getUrl(CacheElement $cacheElement)
    {
        return $this->router->generate('sonata_page_esi_cache', $cacheElement->getKeys(), true);
    }
}