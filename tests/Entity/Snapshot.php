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

namespace Sonata\PageBundle\Tests\Entity;

use Sonata\PageBundle\Entity\BaseSnapshot;

final class Snapshot extends BaseSnapshot
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @return int $id
     */
    public function getId(): int
    {
        return $this->id;
    }
}
