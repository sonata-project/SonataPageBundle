<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Cache;

use Sonata\BlockBundle\Block\BlockContextManagerInterface;
use Sonata\BlockBundle\Block\BlockRendererInterface;
use Sonata\Cache\CacheElement;
use Sonata\CacheBundle\Adapter\SsiCache;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Cache block through an ssi statement.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BlockSsiCache extends SsiCache
{
    /**
     * @var BlockRendererInterface
     */
    protected $blockRenderer;

    /**
     * @var array
     */
    protected $managers;

    /**
     * @var BlockContextManagerInterface
     */
    protected $contextManager;

    /**
     * @param string                       $token
     * @param RouterInterface              $router
     * @param BlockRendererInterface       $blockRenderer
     * @param BlockContextManagerInterface $contextManager Block Context manager
     * @param array                        $managers
     */
    public function __construct($token, RouterInterface $router, BlockRendererInterface $blockRenderer, BlockContextManagerInterface $contextManager, array $managers = [])
    {
        parent::__construct($token, $router, null);

        $this->managers = $managers;
        $this->blockRenderer = $blockRenderer;
        $this->contextManager = $contextManager;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $keys)
    {
        $this->validateKeys($keys);

        $keys['_token'] = $this->computeHash($keys);

        $content = sprintf('<!--# include virtual="%s" -->', $this->router->generate('sonata_page_cache_ssi', $keys, UrlGeneratorInterface::ABSOLUTE_PATH));

        return new CacheElement($keys, new Response($content));
    }

    /**
     * {@inheritdoc}
     */
    public function set(array $keys, $data, $ttl = CacheElement::DAY, array $contextualKeys = [])
    {
        $this->validateKeys($keys);

        return new CacheElement($keys, $data, $ttl, $contextualKeys);
    }

    /**
     * {@inheritdoc}
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

        $block = $manager->getBlock($request->get('block_id'));

        $blockContext = $this->contextManager->get($block);

        $response = $this->blockRenderer->render($blockContext);

        $response->headers->set('x-sonata-page-not-decorable', true);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function computeHash(array $keys)
    {
        // values are casted into string for non numeric id
        return hash('sha256', $this->token.serialize([
            'manager' => (string) $keys['manager'],
            'page_id' => (string) $keys['page_id'],
            'block_id' => (string) $keys['block_id'],
            'updated_at' => (string) $keys['updated_at'],
        ]));
    }

    /**
     * @throws \RuntimeException
     *
     * @param array $keys
     */
    private function validateKeys(array $keys)
    {
        foreach (['block_id', 'page_id', 'manager', 'updated_at'] as $key) {
            if (!isset($keys[$key])) {
                throw new \RuntimeException(sprintf('Please define a `%s` key', $key));
            }
        }
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
