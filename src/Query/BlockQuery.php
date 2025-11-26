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

namespace Sonata\PageBundle\Query;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class BlockQuery
{
    public function __construct(
        #[Assert\Positive]
        #[Assert\Type('integer')]
        #[SerializedName('block_id')]
        public readonly int $blockId,
        #[Assert\Positive]
        #[SerializedName('parent_id')]
        #[Assert\Type('integer')]
        public readonly int $parentId,
    ) {
    }
}
