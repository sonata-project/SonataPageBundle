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
 * @phpstan-type BlockContent array{
 *   id: int|string|null,
 *   name?: string|null,
 *   enabled: boolean,
 *   position: int|null,
 *   settings: array<string, mixed>,
 *   type: string,
 *   created_at: int|string|null,
 *   updated_at: int|string|null,
 *   parent_id?: int|string|null,
 *   blocks: array<array{
 *     id: int|string|null,
 *     name?: string|null,
 *     enabled: boolean,
 *     position: int|null,
 *     settings: array<string, mixed>,
 *     type: string,
 *     created_at: numeric-string|null,
 *     updated_at: numeric-string|null,
 *     blocks: array<string, mixed>,
 *   }>,
 * }
 *
 * @phpstan-type PageContent array{
 *   id: int|string|null,
 *   parent_id?: int|string|null,
 *   javascript: string|null,
 *   stylesheet: string|null,
 *   raw_headers: string|null,
 *   title?: string|null,
 *   meta_description: string|null,
 *   meta_keyword: string|null,
 *   name: string|null,
 *   slug: string|null,
 *   template_code: string|null,
 *   request_method: string|null,
 *   created_at: numeric-string|null,
 *   updated_at: numeric-string|null,
 *   blocks: array<BlockContent>,
 * }
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
     * @param array<string, mixed> $content
     *
     * @return PageBlockInterface
     *
     * @phpstan-param BlockContent $content
     */
    public function loadBlock(array $content, PageInterface $page);
}
