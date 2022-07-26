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
 *
 * @phpstan-type Container array{
 *   name: string,
 *   type: int,
 *   blocks: array<string>,
 *   placement: array{
 *     x?: int,
 *     y?: int|float,
 *     width?: int,
 *     height?: int|float,
 *     right?: int|float,
 *     bottom?: int|float
 *   },
 *   shared: boolean
 * }
 */
final class Template
{
    public const TYPE_STATIC = 1;
    public const TYPE_DYNAMIC = 2;

    private string $path;

    private string $name;

    /**
     * @var array<string, mixed>
     *
     * @phpstan-var array<Container>
     */
    private array $containers = [];

    /**
     * @param string               $name
     * @param string               $path
     * @param array<string, mixed> $containers
     */
    public function __construct($name, $path, array $containers = [])
    {
        $this->name = $name;
        $this->path = $path;

        foreach ($containers as $code => $container) {
            $this->containers[$code] = $this->normalize($container);
        }
    }

    /**
     * @return array<string, mixed>
     *
     * @phpstan-return array<string, Container>
     */
    public function getContainers()
    {
        return $this->containers;
    }

    /**
     * The meta array is an array containing the
     *    - area name.
     *
     * @param string               $code
     * @param array<string, mixed> $meta
     */
    public function addContainer($code, $meta): void
    {
        $this->containers[$code] = $this->normalize($meta);
    }

    /**
     * @param string $code
     *
     * @return array<string, mixed>
     *
     * @phpstan-return Container|array{}
     */
    public function getContainer($code)
    {
        if (isset($this->containers[$code])) {
            return $this->containers[$code];
        }

        return [];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param array<string, mixed> $meta
     *
     * @return array<string, mixed>
     *
     * @phpstan-return Container
     */
    private function normalize(array $meta)
    {
        return [
            'name' => $meta['name'] ?? 'n/a',
            'type' => $meta['type'] ?? self::TYPE_STATIC,
            'blocks' => $meta['blocks'] ?? [],            // default block to be created
            'placement' => $meta['placement'] ?? [],
            'shared' => $meta['shared'] ?? false,
        ];
    }
}
