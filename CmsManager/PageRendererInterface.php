<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\CmsManager;

use Sonata\PageBundle\Model\PageInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * The PageRendererInterface defines methods to render a Page
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface PageRendererInterface
{
    /**
     * @param PageInterface $page
     * @param array         $params
     * @param Response      $response
     *
     * @return Response
     */
    function render(PageInterface $page, array $params = array(), Response $response = null);

    /**
     * @return string
     */
    function getDefaultTemplateCode();

    /**
     * @param string $code
     */
    function setDefaultTemplateCode($code);

    /**
     * @param array $templates
     */
    function setTemplates($templates);

    /**
     * @return array
     */
    function getTemplates();

    /**
     * @param string $code
     *
     * @return string
     */
    function getTemplate($code);
}