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

use Sonata\PageBundle\Template\Matrix\Parser;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-import-type Placement from Parser
 *
 * @phpstan-type Container array{
 *   name: string,
 *   type: int,
 *   blocks: array<string>,
 *   placement: Placement|null,
 *   shared: boolean
 * }
 */
final class Template
{
    public const TYPE_STATIC = 1;
    public const TYPE_DYNAMIC = 2;

    /**
     * @var array<string, mixed>
     *
     * @phpstan-var array<Container>
     */
    private array $containers = [];

    /**
     * @param array<string, array{
     *   name?: string,
     *   type?: int,
     *   blocks?: array<string>,
     *   placement?: Placement,
     *   shared?: bool
     * }> $containers
     */
    public function __construct(
        private string $name,
        private string $path,
        array $containers = [],
    ) {
        foreach ($containers as $code => $container) {
            $this->containers[$code] = $this->normalize($container);
        }
    }

    /**
     * @return array<string, mixed>
     *
     * @phpstan-return array<string, Container>
     */
    public function getContainers(): array
    {
        return $this->containers;
    }

    /**
     * The meta array is an array containing the
     *    - area name.
     *
     * @param array{
     *   name?: string,
     *   type?: int,
     *   blocks?: array<string>,
     *   placement?: Placement,
     *   shared?: bool
     * } $meta
     */
    public function addContainer(string $code, array $meta): void
    {
        $this->containers[$code] = $this->normalize($meta);
    }

    /**
     * @return array<string, mixed>|null
     *
     * @phpstan-return Container|null
     */
    public function getContainer(string $code): ?array
    {
        return $this->containers[$code] ?? null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param array{
     *   name?: string,
     *   type?: int,
     *   blocks?: array<string>,
     *   placement?: Placement,
     *   shared?: bool
     * } $meta
     *
     * @return array<string, mixed>
     *
     * @phpstan-return Container
     */
    private function normalize(array $meta): array
    {
        return [
            'name' => $meta['name'] ?? 'n/a',
            'type' => $meta['type'] ?? self::TYPE_STATIC,
            'blocks' => $meta['blocks'] ?? [], // default block to be created
            'placement' => $meta['placement'] ?? null,
            'shared' => $meta['shared'] ?? false,
        ];
    }
}
