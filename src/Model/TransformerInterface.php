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
 *
 * NEXT_MAJOR: Restrict and simplify BlockContent type:
 *   - position: int|null
 *   - enabled: bool
 *
 * @phpstan-type BlockContent array{
 *   id?: int|string|null,
 *   name?: string|null,
 *   enabled: bool|'1'|'0',
 *   position: int|string|null,
 *   settings: array<string, mixed>,
 *   type: string|null,
 *   created_at?: int|numeric-string|null,
 *   updated_at?: int|numeric-string|null,
 *   parent_id?: int|string|null,
 *   blocks: array<array{
 *     id?: int|string|null,
 *     name?: string|null,
 *     enabled: bool|'1'|'0',
 *     position: int|string|null,
 *     settings: array<string, mixed>,
 *     type: string|null,
 *     created_at?: int|numeric-string|null,
 *     updated_at?: int|numeric-string|null,
 *     parent_id?: int|string|null,
 *     blocks: array<mixed>,
 *   }>,
 * }
 * @phpstan-type PageContent array{
 *   id?: int|string|null,
 *   parent_id?: int|string|null,
 *   javascript?: string|null,
 *   stylesheet?: string|null,
 *   raw_headers?: string|null,
 *   title?: string|null,
 *   meta_description?: string|null,
 *   meta_keyword?: string|null,
 *   name?: string|null,
 *   slug?: string|null,
 *   template_code?: string|null,
 *   request_method?: string|null,
 *   created_at?: int|numeric-string|null,
 *   updated_at?: int|numeric-string|null,
 *   blocks: array<BlockContent>,
 * }
 */
interface TransformerInterface
{
    public function load(SnapshotInterface $snapshot): PageInterface;

    public function create(PageInterface $page, ?SnapshotInterface $snapshot = null): SnapshotInterface;

    /**
     * @return Collection<array-key, PageInterface>
     */
    public function getChildren(PageInterface $page): Collection;

    /**
     * @param array<string, mixed> $content
     *
     * @phpstan-param BlockContent $content
     */
    public function loadBlock(array $content, PageInterface $page): PageBlockInterface;
}
