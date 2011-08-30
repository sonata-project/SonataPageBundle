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
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    protected $servers;

    protected $container;

    public function __construct(array $servers = array(), Router $router, ContainerInterface $container = null)
    {
        $this->servers   = $servers;
        $this->router    = $router;
        $this->container = $container;
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
        foreach ($this->servers as $server) {
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
        foreach ($keys as $key => $value) {
            $parameters[] = sprintf('obj.http.%s ~ %s', $this->normalize($key), $value);
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

        return new Response($content);
    }

    public function set(CacheElement $cacheElement)
    {
        // todo : prefetch the url ?
    }

    protected function getUrl(CacheElement $cacheElement)
    {
        return $this->router->generate('sonata_page_esi_cache', $cacheElement->getKeys(), true);
    }

    protected function normalize($key)
    {
        $key = strtolower($key);
        return sprintf('x-sonata-%s', str_replace(array('_', '\\'), '-', $key));
    }

    public function cacheAction()
    {
        $request = $this->container->get('request');
        if ($request->get('manager') == 'page') {
            $manager = $this->container->get('sonata.page.cms.page');
        } else {
            $manager = $this->container->get('sonata.page.cms.snapshot');
        }

        $page    = $manager->getPageById($request->get('page_id'));
        $block   = $manager->getBlock($request->get('block_id'));

        if (!$page || !$block) {
            return new Response('', 404);
        }

        $recorder = $manager->getRecorder();
        if ($recorder) {
            $recorder->reset();
        }

        $response = $manager->renderBlock($block, $page, false);

        if ($request->get('handler') == 'page') {
            $response->setPrivate();
        } else {
            $response->setPublic();

            $headers = array(
                'x-sonata-block-id'    => $block->getId(),
                'x-sonata-page-id'     => $page->getId(),
                'x-sonata-block-type'  => $block->getType(),
                'x-sonata-page-route'  => $page->getRouteName(),
            );

            if ($recorder) {
                foreach ($recorder->get() as $name => $keys) {
                    $keys = array_map('strval', $keys);
                    $headers[$this->normalize($name)] = json_encode($keys);
                }
            }

            $response->headers->add($headers);
        }

        if ($recorder) {
            $recorder->reset();
        }

        return $response;
    }

    public function isContextual()
    {
        return true;
    }
}