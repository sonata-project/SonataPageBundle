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
     * @param array<string, mixed> $parameters
     */
    public function renderResponse(?string $code, array $parameters = [], ?Response $response = null): Response;

    public function add(string $code, Template $template): void;

    public function get(string $code): ?Template;

    public function setDefaultTemplateCode(string $code): void;

    public function getDefaultTemplateCode(): string;

    /**
     * @param array<Template> $templates
     */
    public function setAll(array $templates): void;

    /**
     * @return array<Template>
     */
    public function getAll(): array;
}
