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

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface BlockInteractorInterface
{
    /**
     * Return a block with the given id.
     *
     * @param int|string $id
     *
     * @return PageBlockInterface|null
     */
    public function getBlock($id);

    /**
     * Return a flat list if page's blocks.
     *
     * @return array<PageBlockInterface>
     */
    public function getBlocksById(PageInterface $page);

    /**
     * Load blocks attached the given page.
     *
     * @return array<PageBlockInterface>
     */
    public function loadPageBlocks(PageInterface $page);

    /**
     * Save the blocks positions. Partial references are allowed.
     * Better for performance, but can lead to query issues.
     *
     * @param array<array{
     *   id?: int|string|null,
     *   position?: int,
     *   parent_id?: int|string|null,
     *   page_id?: int|string|null,
     * }> $data
     * @param bool $partial
     *
     * @return bool
     */
    public function saveBlocksPosition(array $data = [], $partial = true);

    /**
     * @param array{
     *   name?: string|null,
     *   enabled?: boolean,
     *   page?: PageInterface,
     *   code: string,
     *   position?: int,
     *   parent?: PageBlockInterface|null,
     * } $values An array of values for container creation
     *
     * @return PageBlockInterface
     */
    public function createNewContainer(array $values);
}
