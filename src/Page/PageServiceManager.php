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

namespace Sonata\PageBundle\Page;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\Service\PageServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Manages all page services and the execution workflow of a page.
 *
 * The rendering of a page is delegated to the page service. Usually, the page service will use the template manager
 * to handle the page rendering but it may also implement an alternate rendering method.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class PageServiceManager implements PageServiceManagerInterface
{
    /**
     * @var array<PageServiceInterface>
     */
    private array $services = [];

    private ?PageServiceInterface $default = null;

    public function add(string $type, PageServiceInterface $service): void
    {
        $this->services[$type] = $service;
    }

    public function get($type): PageServiceInterface
    {
        if ($type instanceof PageInterface) {
            $type = $type->getType();
        }

        if (!isset($this->services[$type])) {
            if (null === $this->default) {
                throw new \RuntimeException(sprintf('unable to find a default service for type "%s"', $type ?? ''));
            }

            return $this->default;
        }

        return $this->services[$type];
    }

    public function getAll(): array
    {
        return $this->services;
    }

    public function setDefault(PageServiceInterface $service): void
    {
        $this->default = $service;
    }

    public function execute(PageInterface $page, Request $request, array $parameters = [], ?Response $response = null): Response
    {
        $service = $this->get($page);

        $response ??= new Response('', 200, $page->getHeaders());

        if ($response->isRedirection()) {
            return $response;
        }

        $parameters['page'] = $page;
        $parameters['site'] = $page->getSite();

        $response = $service->execute($page, $request, $parameters, $response);

        return $response;
    }
}
