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

interface PageRendererInterface
{
    /**
     * @param \Sonata\PageBundle\Model\PageInterface          $page
     * @param array                                           $params
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render(PageInterface $page, array $params = array(), Response $response = null);

    /**
     * @return string
     */
    public function getDefaultTemplateCode();

    /**
     * @param string $code
     */
    public function setDefaultTemplateCode($code);

    /**
     * @param array $templates
     */
    public function setTemplates($templates);

    /**
     * @return array
     */
    public function getTemplates();

    /**
     * @param string $code
     *
     * @return string
     */
    public function getTemplate($code);
}