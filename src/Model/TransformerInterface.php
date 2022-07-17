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

use Doctrine\Common\Collections\Collection;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface TransformerInterface
{
    /**
     * @return PageInterface
     */
    public function load(SnapshotInterface $snapshot);

    /**
     * @return SnapshotInterface
     */
    public function create(PageInterface $page);

    /**
     * @return Collection<array-key, PageInterface>
     */
    public function getChildren(PageInterface $page);

    /**
     * @return PageBlockInterface
     */
    public function loadBlock(array $content, PageInterface $page);
}
