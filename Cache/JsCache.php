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
use Symfony\Component\DependencyInjection\ContainerInterface;

class JsCache implements CacheInterface
{
    protected $router;

    protected $sync;

    protected $container;

    /**
     * @param \Symfony\Component\Routing\Router $router
     * @param bool $sync
     */
    public function __construct(Router $router, $sync = false, ContainerInterface $container = null)
    {
        $this->router = $router;
        $this->sync   = $sync;
        $this->container = $container;
    }

    public function flushAll()
    {
        return true;
    }

    public function flush(array $keys = array())
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
        return new Response($this->sync ? $this->getSync($cacheElement) : $this->getAsync($cacheElement));
    }

    protected function getSync(CacheElement $cacheElement)
    {
        $keys = $cacheElement->getKeys();
        return sprintf(<<<CONTENT
<div id="block-cms-%s" >
<script type="text/javascript">
  /*<![CDATA[*/
    (function () {
      var block, xhr;
      block = document.getElementById('block-cms-%s');
      if (window.XMLHttpRequest) {
        xhr = new XMLHttpRequest();
      } else {
        xhr = new ActiveXObject('Microsoft.XMLHTTP');
      }

      xhr.open('GET', '%s', false);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.send('');

      // create an empty element
      var div = document.createElement("div");
      div.innerHTML = xhr.responseText;

      for (var node in div.childNodes) {
        if (div.childNodes[node] && div.childNodes[node].nodeType == 1) {
          block.parentNode.replaceChild(div.childNodes[node], block);
        }
      }
    })();
  /*]]>*/
</script>
</div>
CONTENT
, $keys['block_id'], $keys['block_id'], $this->getUrl('sonata_page_js_sync_cache', $cacheElement));
    }

    protected function getAsync(CacheElement $cacheElement)
    {
        $keys = $cacheElement->getKeys();
        return sprintf(<<<CONTENT
<div id="block-cms-%s" >
  <script type="text/javascript">
    /*<![CDATA[*/

      (function() {
        var b = document.createElement('script');
        b.type = 'text/javascript';
        b.async = true;
        b.src = '%s'
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(b, s);
      })();

    /*]]>*/
  </script>
</div>
CONTENT
, $keys['block_id'], $this->getUrl('sonata_page_js_async_cache', $cacheElement));
    }

    public function set(CacheElement $cacheElement)
    {
        // todo : nothing to do
    }

    public function getUrl($name, CacheElement $cacheElement)
    {
        $parameters = $cacheElement->getKeys();
        return $this->router->generate($name, $parameters, true);
    }

    public function cacheAction()
    {
        if (!$this->container) {
            throw new \RunTimeException('Please define a ContainerInterface instance');
        }

        $request = $this->container->get('request');

        $securityContext = $this->container->get('security.context');

        if ($securityContext->isGranted('ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT')) {
            $manager = $this->container->get('sonata.page.cms.page');
        } else {
            $manager = $this->container->get('sonata.page.cms.snapshot');
        }

        $page    = $manager->getPageById($request->get('page_id'));
        $block   = $manager->getBlock($request->get('block_id'));

        if (!$page || !$block) {
            return new Response('', 404);
        }

        $response = $manager->renderBlock($block, $page, false);
        $response->setPrivate(); //  always set to private

        if ($this->sync) {
            return $response;
        }

        $response->setContent(sprintf(<<<JS
    (function () {
      var block = document.getElementById('block-cms-%s');

      var div = document.createElement("div");
      div.innerHTML = %s;

      for (var node in div.childNodes) {
        if (div.childNodes[node] && div.childNodes[node].nodeType == 1) {
          block.parentNode.replaceChild(div.childNodes[node], block);
        }
      }
    })();
JS
, $block->getId(), json_encode($response->getContent())));

        $response->headers->set('Content-Type', 'application/javascript');

        return $response;
    }

    public function isContextual()
    {
        return false;
    }
}