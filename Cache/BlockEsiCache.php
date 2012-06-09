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

use Sonata\CacheBundle\Cache\CacheInterface;
use Sonata\CacheBundle\Cache\CacheElement;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;

/**
 * Cache block through an esi statement
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BlockEsiCache implements CacheInterface
{
    protected $router;

    protected $servers;

    protected $blockService;

    protected $managers;

    /**
     * @param array                                                  $servers
     * @param \Symfony\Component\Routing\RouterInterface             $router
     * @param \Sonata\BlockBundle\Block\BlockServiceManagerInterface $blockService
     * @param array                                                  $managers
     */
    public function __construct(array $servers = array(), RouterInterface $router, BlockServiceManagerInterface $blockService, array $managers = array())
    {
        $this->servers      = $servers;
        $this->router       = $router;
        $this->blockService = $blockService;
        $this->managers     = $managers;
    }

    /**
     * {@inheritdoc}
     */
    public function flushAll()
    {
        return $this->runCommand('purge', 'req.url ~ .*');
    }

    /**
     * @param string $command
     * @param string $expression
     *
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

    /**
     * {@inheritdoc}
     */
    public function flush(array $keys = array())
    {
        $parameters = array();
        foreach ($keys as $key => $value) {
            $parameters[] = sprintf('obj.http.%s ~ %s', $this->normalize($key), $value);
        }

        $purge = implode(" && ", $parameters);

        return $this->runCommand('purge', $purge);
    }

    /**
     * {@inheritdoc}
     */
    public function has(array $keys)
    {
        return true;
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
     * @param string $key
     *
     * @return string
     */
    protected function normalize($key)
    {
        return sprintf('x-sonata-cache-%s', str_replace(array('_', '\\'), '-', strtolower($key)));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
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

        return $this->blockService->renderBlock($manager->getBlock($request->get('block_id')));
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
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

    /**
     * {@inheritdoc}
     */
    public function isContextual()
    {
        return true;
    }
}