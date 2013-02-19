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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;

use Sonata\CacheBundle\Cache\CacheElement;
use Sonata\BlockBundle\Block\BlockRendererInterface;
use Sonata\CacheBundle\Adapter\EsiCache;

/**
 * Cache block through an esi statement
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BlockEsiCache extends EsiCache
{
    protected $blockRenderer;

    protected $managers;

    /**
     * @param array                  $servers
     * @param RouterInterface        $router
     * @param BlockRendererInterface $blockService
     * @param array                  $managers
     */
    public function __construct($token, array $servers = array(), RouterInterface $router, BlockRendererInterface $blockRenderer, array $managers = array())
    {
        parent::__construct($token, $servers, $router, null);

        $this->blockRenderer = $blockRenderer;
        $this->managers      = $managers;
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
                throw new \RuntimeException(sprintf('Please define a `%s` key', $key));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $keys)
    {
        $this->validateKeys($keys);

        $keys['_token'] = $this->computeHash($keys);

        $content = sprintf('<esi:include src="%s" />', $this->router->generate('sonata_page_cache_esi', $keys, true));

        return new CacheElement($keys, new Response($content));
    }

    /**
     * {@inheritdoc}
     */
    public function set(array $keys, $data, $ttl = 84600, array $contextualKeys = array())
    {
        $this->validateKeys($keys);

        return new CacheElement($keys, $data, $ttl, $contextualKeys);
    }

    /**
     * @param array $keys
     *
     * @return string
     */
    protected function computeHash(array $keys)
    {
        // values are casted into string for non numeric id
        return hash('sha256', $this->token.serialize(array(
            'manager'    => (string) $keys['manager'],
            'page_id'    => (string) $keys['page_id'],
            'block_id'   => (string) $keys['block_id'],
            'updated_at' => (string) $keys['updated_at'],
        )));
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function cacheAction(Request $request)
    {
        $parameters = array_merge($request->query->all(), $request->attributes->all());

        if ($request->get('_token') != $this->computeHash($parameters)) {
            throw new AccessDeniedHttpException('Invalid token');
        }

        $manager = $this->getManager($request);

        $page = $manager->getPageById($request->get('page_id'));

        if (!$page) {
            throw new NotFoundHttpException(sprintf('Page not found : %s', $request->get('page_id')));
        }

        return $this->blockRenderer->render($manager->getBlock($request->get('block_id')));
    }

    /**
     * @throws NotFoundHttpException
     *
     * @param Request $request
     *
     * @return \Sonata\PageBundle\CmsManager\CmsManagerInterface
     */
    private function getManager(Request $request)
    {
        if (!isset($this->managers[$request->get('manager')])) {
            throw new NotFoundHttpException(sprintf('The manager `%s` does not exist', $request->get('manager')));
        }

        return $this->managers[$request->get('manager')];
    }
}
