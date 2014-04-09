<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Model;

/**
 * Template
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class Template
{
    protected $path;

    protected $name;

    protected $containers;

    const TYPE_STATIC = 1;

    const TYPE_DYNAMIC = 2;

    /**
     * @param string $name
     * @param string $path
     * @param array  $containers
     */
    public function __construct($name, $path, array $containers = array())
    {
        $this->name       = $name;
        $this->path       = $path;
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
     *    - area name
     *
     * @param string $code
     * @param array  $meta
     */
    public function addContainer($code, $meta)
    {
        $this->containers[$code] = $this->normalize($meta);
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    protected function normalize(array $meta)
    {
        return array(
            'name'      => isset($meta['name'])      ? $meta['name']      : 'n/a',
            'type'      => isset($meta['type'])      ? $meta['type']      : self::TYPE_STATIC,
            'blocks'    => isset($meta['blocks'])    ? $meta['blocks']    : array(),            // default block to be created
            'placement' => isset($meta['placement']) ? $meta['placement'] : array(),
            'shared'    => isset($meta['shared'])    ? $meta['shared']    : false,
        );
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
}
