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

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sonata\BlockBundle\Block\BlockContextManagerInterface;
use Sonata\BlockBundle\Block\BlockRendererInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\Cache\CacheAdapterInterface;
use Sonata\Cache\CacheElement;


/**
 * Cache a block through a Javascript code
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BlockJsCache implements CacheAdapterInterface
{
    protected $router;

    protected $sync;

    protected $cmsSelector;

    protected $blockRenderer;

    protected $contextManager;

    /**
     * @param RouterInterface              $router
     * @param CmsManagerSelectorInterface  $cmsSelector
     * @param BlockRendererInterface       $blockRenderer
     * @param BlockContextManagerInterface $contextManager
     * @param bool                         $sync
     */
    public function __construct(RouterInterface $router, CmsManagerSelectorInterface $cmsSelector, BlockRendererInterface $blockRenderer, BlockContextManagerInterface $contextManager, $sync = false)
    {
        $this->router        = $router;
        $this->sync          = $sync;
        $this->cmsSelector   = $cmsSelector;
        $this->blockRenderer = $blockRenderer;
        $this->contextManager = $contextManager;
    }

    /**
     * {@inheritdoc}
     */
    public function flushAll()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(array $keys = array())
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function has(array $keys)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $keys)
    {
        $this->validateKeys($keys);

        return new CacheElement($keys, new Response($this->sync ? $this->getSync($keys) : $this->getAsync($keys)));
    }

    /**
     * @throws \RuntimeException
     *
     * @param array $keys
     *
     * @return void
     */
    private function validateKeys(array $keys)
    {
        foreach (array('block_id', 'page_id', 'manager', 'updated_at') as $key) {
            if (!isset($keys[$key])) {
                throw new \RuntimeException(sprintf('Please define a `%s` key, provided: %s', $key, json_encode(array_keys($keys))));
            }
        }
    }

    /**
     * @param array $keys
     *
     * @return string
     */
    protected function getSync(array $keys)
    {
        return sprintf(
'<div id="block-cms-%s" >
    <script>
        /*<![CDATA[*/
            (function () {
                var block, xhr;
                block = document.getElementById("block-cms-%s");
                if (window.XMLHttpRequest) {
                    xhr = new XMLHttpRequest();
                } else {
                    xhr = new ActiveXObject("Microsoft.XMLHTTP");
                }

                xhr.open("GET", "%s", false);
                xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                xhr.send("");

                // create an empty element
                var div = document.createElement("div");
                div.innerHTML = xhr.responseText;

                fo  r (var node in div.childNodes) {
                    if (div.childNodes[node] && div.childNodes[node].nodeType == 1) {
                        block.parentNode.replaceChild(div.childNodes[node], block);
                    }
                }
            })();
        /*]]>*/
    </script>
</div>'

, $keys['block_id'], $keys['block_id'], $this->router->generate('sonata_page_js_sync_cache', $keys, true));
    }

    /**
     * @param  array  $keys
     * @return string
     */
    protected function getAsync(array $keys)
    {
        return sprintf(
'<div id="block-cms-%s" >
    <script>
        /*<![CDATA[*/
            (function() {
                var b = document.createElement("script");
                b.type = "text/javascript";
                b.async = true;
                b.src = "%s"
                var s = document.getElementsByTagName("script")[0];
                s.parentNode.insertBefore(b, s);
            })();

        /*]]>*/
    </script>
</div>'
, $keys['block_id'], $this->router->generate('sonata_page_js_async_cache', $keys, true));
    }

    /**
     * {@inheritdoc}
     */
    public function set(array $keys, $data, $ttl = CacheElement::DAY, array $contextualKeys = array())
    {
        $this->validateKeys($keys);

        return new CacheElement($keys, $data, $ttl, $contextualKeys);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cacheAction(Request $request)
    {
        $cms = $this->cmsSelector->retrieve();

        try {
            $page = $cms->getPageById($request->get('page_id'));
        } catch (PageNotFoundException $e) {
            $page = false;
        }

        $block = $cms->getBlock($request->get('block_id'));

        if (!$page || !$block) {
            return new Response('', 404);
        }

        $options = array();

        $blockContext = $this->contextManager->get($block, $options);
        $response = $this->blockRenderer->render($blockContext);

        $response->setPrivate(); //  always set to private

        if ($this->sync) {
            return $response;
        }

        $response->setContent(sprintf('
    (function () {
        var block = document.getElementById("block-cms-%s");

        var div = document.createElement("div");
        div.innerHTML = %s;

        for (var node in div.childNodes) {
            if (div.childNodes[node] && div.childNodes[node].nodeType == 1) {
                block.parentNode.replaceChild(div.childNodes[node], block);
            }
        }
    })();
'
, $block->getId(), json_encode($response->getContent())));

        $response->headers->set('Content-Type', 'application/javascript');

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function isContextual()
    {
        return false;
    }
}
