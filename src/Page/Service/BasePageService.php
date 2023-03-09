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

namespace Sonata\PageBundle\Page\Service;

/**
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
abstract class BasePageService implements PageServiceInterface
{
    public function __construct(protected string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }
}
