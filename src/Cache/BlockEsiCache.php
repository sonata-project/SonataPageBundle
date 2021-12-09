<?php

declare(strict_types=1);

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
use Sonata\Cache\CacheElementInterface;
use Sonata\Cache\Invalidation\Recorder;
use Sonata\CacheBundle\Adapter\VarnishCache;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Cache block through an esi statement.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @final since sonata-project/page-bundle 3.x
 */
class BlockEsiCache extends VarnishCache
{
    /**
     * @var BlockRendererInterface
     */
    protected $blockRenderer;

    /**
     * @var CmsManagerInterface[]
     */
    protected $managers;

    /**
     * @var BlockContextManagerInterface
     */
    protected $contextManager;

    /**
     * @var Recorder
     */
    protected $recorder;

    public function __construct(
        string $token,
        array $servers,
        RouterInterface $router,
        string $purgeInstruction,
        ControllerResolverInterface $resolver,
        ArgumentResolverInterface $argumentResolver,
        BlockRendererInterface $blockRenderer,
        BlockContextManagerInterface $contextManager,
        array $managers = [],
        ?Recorder $recorder = null
    ) {
        parent::__construct($token, $servers, $router, $purgeInstruction, $resolver, $argumentResolver);

        $this->blockRenderer = $blockRenderer;
        $this->managers = $managers;
        $this->contextManager = $contextManager;
        $this->recorder = $recorder;
    }

    public function get(array $keys): CacheElementInterface
    {
        $this->validateKeys($keys);

        $keys['_token'] = $this->computeHash($keys);

        $content = sprintf('<esi:include src="%s" />', $this->router->generate('sonata_page_cache_esi', $keys, UrlGeneratorInterface::ABSOLUTE_URL));

        return new CacheElement($keys, new Response($content));
    }

    public function set(array $keys, $data, int $ttl = CacheElement::DAY, array $contextualKeys = []): CacheElementInterface
    {
        $this->validateKeys($keys);

        return new CacheElement($keys, $data, $ttl, $contextualKeys);
    }

    public function cacheAction(Request $request)
    {
        $parameters = array_merge($request->query->all(), $request->attributes->all());

        if ($request->get('_token') !== $this->computeHash($parameters)) {
            throw new AccessDeniedHttpException('Invalid token');
        }

        $manager = $this->getManager($request);

        $page = $manager->getPageById($request->get('page_id'));

        if (!$page) {
            throw new NotFoundHttpException(sprintf('Page not found : %s', $request->get('page_id')));
        }

        $block = $manager->getBlock($request->get('block_id'));

        $blockContext = $this->contextManager->get($block);

        if ($this->recorder) {
            $this->recorder->add($blockContext->getBlock());
            $this->recorder->push();
        }

        $response = $this->blockRenderer->render($blockContext);

        if ($this->recorder) {
            $keys = $this->recorder->pop();
            $keys['page_id'] = $page->getId();
            $keys['block_id'] = $block->getId();

            foreach ($keys as $key => $value) {
                $response->headers->set($this->normalize($key), $value);
            }
        }

        $response->headers->set('x-sonata-page-not-decorable', true);

        return $response;
    }

    protected function computeHash(array $keys): string
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
