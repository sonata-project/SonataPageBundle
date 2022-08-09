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

interface PageServiceManagerInterface
{
    public function add(string $type, PageServiceInterface $service): void;

    /**
     * @param string|PageInterface $type
     */
    public function get($type): PageServiceInterface;

    /**
     * @return array<PageServiceInterface>
     */
    public function getAll(): array;

    public function setDefault(PageServiceInterface $service): void;

    /**
     * Executes the page. This method acts as a controller's action for a specific page and is therefor expected
     * to return a Response object.
     *
     * @param array<string, mixed> $parameters An array of view parameters. In the case of hybrid pages, it may have a
     *                                         parameter "content" that contains the view result of the controller
     */
    public function execute(PageInterface $page, Request $request, array $parameters = [], ?Response $response = null): Response;
}
