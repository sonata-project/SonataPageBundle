<?php


namespace Sonata\PageBundle\Cache;

use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Response;

class EsiCache implements CacheInterface
{
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function flushAll()
    {

    }

    public function flush(CacheElement $cacheElement)
    {

    }

    public function has(CacheElement $cacheElement)
    {
        return true;
    }

    /**
     * @param array $parameters
     * @return string
     */
    public function get(CacheElement $cacheElement)
    {
        $content = sprintf('<esi:include src="%s" />',
            $this->getUrl($cacheElement),
            $cacheElement->getTtl()
        );

//        $content .= $this->getUrl($cacheElement);

        $headers = array(
            'x-sonata-block-cache' => json_encode($cacheElement->getKeys())
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