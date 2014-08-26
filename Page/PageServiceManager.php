<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Page;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\Service\PageServiceInterface;
use Sonata\PageBundle\Page\PageServiceManagerInterface;

/**
 * Manages all page services and the execution workflow of a page.
 *
 * The rendering of a page is delegated to the page service. Usually, the page service will use the template manager
 * to handle the page rendering but it may also implement an alternate rendering method.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
class PageServiceManager implements PageServiceManagerInterface
{
    /**
     * @var PageServiceInterface[]
     */
    protected $services = array();

    /**
     * @var PageServiceInterface|null
     */
    protected $default;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * Constructor
     *
     * @param RouterInterface $router Router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function add($type, PageServiceInterface $service)
    {
        $this->services[$type] = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function get($type)
    {
        if ($type instanceof PageInterface) {
            $type = $type->getType();
        }

        if (!isset($this->services[$type])) {

            if (!$this->default) {
                throw new \RuntimeException(sprintf('unable to find a default service for type "%s"', $type));
            }

            return $this->default;
        }

        return $this->services[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        return $this->services;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefault(PageServiceInterface $service)
    {
        $this->default = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(PageInterface $page, Request $request, array $parameters = array(), Response $response = null)
    {
        $service = $this->get($page);

        $response = $response ?: $this->createResponse($page);

        if ($response->isRedirection()) {
            return $response;
        }

        $parameters['page'] = $page;
        $parameters['site'] = $page->getSite();

        $response = $service->execute($page, $request, $parameters, $response);

        return $response;
    }

    /**
     * Creates a base response for given page
     *
     * @param PageInterface $page
     *
     * @return Response
     */
    protected function createResponse(PageInterface $page)
    {
        if ($page->getTarget()) {
            $page->addHeader('Location', $this->router->generate($page->getTarget()));
            $response = new Response('', 302, $page->getHeaders() ?: array());
        } else {
            $response = new Response('', 200, $page->getHeaders() ?: array());
        }

        return $response;
    }
}
