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
 * Template.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class Template
{
    public const TYPE_STATIC = 1;

    public const TYPE_DYNAMIC = 2;
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $containers;

    /**
     * @param string $name
     * @param string $path
     */
    public function __construct($name, $path, array $containers = [])
    {
        $this->name = $name;
        $this->path = $path;
        $this->containers = $containers;

        // force normalization of containers
        foreach ($this->containers as &$container) {
            $container = $this->normalize($container);
        }
    }

    /**
     * @return array
     */
    public function getContainers()
    {
        return $this->containers;
    }

    /**
     * The meta array is an array containing the
     *    - area name.
     *
     * @param string $code
     * @param array  $meta
     */
    public function addContainer($code, $meta): void
    {
        $this->containers[$code] = $this->normalize($meta);
    }

    /**
     * @param $code
     *
     * @return array
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
     * @return array
     */
    protected function normalize(array $meta)
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
