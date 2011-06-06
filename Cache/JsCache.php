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

class JsCache implements CacheInterface
{
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function flushAll()
    {
        return true;
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
        $keys = $cacheElement->getKeys();
        $content = sprintf(<<<CONTENT
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
, $keys['block_id'], $keys['block_id'], $this->getUrl($cacheElement));

        return new Response($content);
    }

    public function createResponse(CacheElement $cacheElement)
    {
        return new Response;
    }

    public function set(CacheElement $cacheElement)
    {
        // todo : nothing to do
    }

    public function getUrl(CacheElement $cacheElement)
    {
        return $this->router->generate('sonata_page_js_cache', $cacheElement->getKeys(), true);
    }
}