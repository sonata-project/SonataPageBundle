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
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\Service\PageServiceInterface;

/**
 * Interface to manage page services.
 */
interface PageServiceManagerInterface
{
    /**
     * Adds a page service for given page type
     *
     * @param string               $type    Page type
     * @param PageServiceInterface $service Service
     */
    public function add($type, PageServiceInterface $service);

    /**
     * Returns the page service for given page
     *
     * @param mixed $type A page type or page object
     *
     * @return PageServiceInterface
     */
    public function get($type);

    /**
     * Returns all page services
     *
     * @return PageServiceInterface[]
     */
    public function getAll();

    /**
     * Sets the default page service
     *
     * @param PageServiceInterface $service
     */
    public function setDefault(PageServiceInterface $service);

    /**
     * Executes the page. This method acts as a controller's action for a specific page and is therefor expected
     * to return a Response object.
     *
     * @param PageInterface $page       Page to execute
     * @param Request       $request    Request object
     * @param array         $parameters An array of view parameters. In the case of hybrid pages, it may have a
     *                                  parameter "content" that contains the view result of the controller.
     * @param Response|null $response Response object
     *
     * @return Response
     */
    public function execute(PageInterface $page, Request $request, array $parameters = array(), Response $response = null);
}
