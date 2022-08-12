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
     * @param int|string $id
     */
    public function getBlock($id): ?PageBlockInterface;

    /**
     * @return array<PageBlockInterface>
     */
    public function getBlocksById(PageInterface $page): array;

    /**
     * @return array<PageBlockInterface>
     */
    public function loadPageBlocks(PageInterface $page): array;

    /**
     * Save the blocks positions. Partial references are allowed.
     * Better for performance, but can lead to query issues.
     *
     * @param array<array{
     *   id?: int|string,
     *   position?: string,
     *   parent_id?: int|string,
     *   page_id?: int|string,
     * }> $data
     */
    public function saveBlocksPosition(array $data = [], bool $partial = true): bool;

    /**
     * @param array{
     *   name?: string|null,
     *   enabled?: boolean,
     *   page?: PageInterface,
     *   code: string,
     *   position?: int,
     *   parent?: PageBlockInterface|null,
     * } $values An array of values for container creation
     */
    public function createNewContainer(array $values): PageBlockInterface;
}
