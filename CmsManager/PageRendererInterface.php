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
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param array $params
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     * @return void
     */
    public function render(PageInterface $page, array $params = array(), Response $response = null);

    /**
     * @return void
     */
    public function getDefaultTemplateCode();

    /**
     * @param $code
     * @return void
     */
    public function setDefaultTemplateCode($code);

    /**
     * @param $templates
     * @return void
     */
    public function setTemplates($templates);

    /**
     * @return void
     */
    public function getTemplates();

    /**
     * @param $code
     * @return void
     */
    public function getTemplate($code);
}