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
use Symfony\Component\Process\Process;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

use Sonata\CacheBundle\Cache\CacheElement;
use Sonata\BlockBundle\Block\BlockRendererInterface;

use Sonata\CacheBundle\Adapter\SsiCache;

/**
 * Cache block through an ssi statement
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BlockSsiCache extends SsiCache
{
    protected $blockRenderer;

    protected $managers;

    /**
     * @param string                 $token
     * @param RouterInterface        $router
     * @param BlockRendererInterface $blockService
     * @param array                  $managers
     */
    public function __construct($token, RouterInterface $router, BlockRendererInterface $blockRenderer, array $managers = array())
    {
        parent::__construct($token, $router, null);

        $this->managers     = $managers;
        $this->blockRenderer = $blockRenderer;
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

        $keys['token'] = $this->computeHash($keys);

        $content = sprintf('<!--# include virtual="%s" -->', $this->router->generate('sonata_page_cache_ssi', $keys, false));

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
     * @param Request $request
     *
     * @return mixed
     */
    public function cacheAction(Request $request)
    {
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
     * @return CmsManagerInterface
     */
    private function getManager(Request $request)
    {
        if (!isset($this->managers[$request->get('manager')])) {
            throw new NotFoundHttpException(sprintf('The manager `%s` does not exist', $request->get('manager')));
        }

        return $this->managers[$request->get('manager')];
    }
}