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

namespace Sonata\PageBundle\Tests\Model;

use Sonata\PageBundle\Model\Site as BaseSite;

final class Site extends BaseSite
{
    public function setId($id): void
    {
        $this->id = $id;
    }
}
