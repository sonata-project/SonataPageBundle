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

use Sonata\PageBundle\Model\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
interface TemplateManagerInterface
{
    /**
     * @param string|null          $code       Template code
     * @param array<string, mixed> $parameters An array of view parameters
     * @param Response             $response   Response to update
     */
    public function renderResponse(?string $code, array $parameters = [], ?Response $response = null): Response;

    /**
     * @param string   $code     Code
     * @param Template $template Template object
     *
     * @return void
     */
    public function add($code, Template $template);

    /**
     * @param string $code
     *
     * @return Template|null
     */
    public function get($code);

    /**
     * @param string $code
     *
     * @return void
     */
    public function setDefaultTemplateCode($code);

    /**
     * @return string
     */
    public function getDefaultTemplateCode();

    /**
     * @param Template[] $templates
     *
     * @return void
     */
    public function setAll($templates);

    /**
     * @return Template[]
     */
    public function getAll();
}
