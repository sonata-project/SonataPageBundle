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

use Sonata\BlockBundle\Model\BlockInterface;

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
     * @return array
     */
    public function getChildren(PageInterface $page);

    /**
     * @return BlockInterface
     */
    public function loadBlock(array $content, PageInterface $page);
}
