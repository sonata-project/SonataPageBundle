<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Page\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sonata\PageBundle\Model\PageInterface;

/**
 * Interface for a page service responsible for the management of pages from a given type.
 *
 * This class acts as a page controller that manages the life-cycle of a page type. It may handle pages that do not
 * necessarily have a symfony route or a symfony controller, such as pure cms pages, but that still require some
 * processing such as data loading or http headers.
 *
 * For other page types, this service may contain the action that would normally be set within a controller, and may
 * also provide additional information or behavior related to this type of pages.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
interface PageServiceInterface
{
    /**
     * Returns the page service name
     *
     * @return string
     */
    public function getName();

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
