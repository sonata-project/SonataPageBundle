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

namespace Sonata\PageBundle\Model;

use Doctrine\Common\Collections\AbstractLazyCollection;

/**
 * @extends AbstractLazyCollection<array-key, PageInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @see $collection property is initialized in the doInitialize method
 */
final class SnapshotChildrenCollection extends AbstractLazyCollection
{
    public function __construct(
        private TransformerInterface $transformer,
        private PageInterface $page,
    ) {
    }

    protected function doInitialize(): void
    {
        $this->collection = $this->transformer->getChildren($this->page);
    }
}
