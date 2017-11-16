<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Model;

final class SnapshotPageProxyFactory implements SnapshotPageProxyFactoryInterface
{
    /**
     * @var string
     */
    private $snapshotPageProxyClass;

    /**
     * SnapshotPageProxyFactory constructor.
     *
     * @param string $snapshotPageProxyClass class name
     */
    public function __construct($snapshotPageProxyClass)
    {
        $this->snapshotPageProxyClass = $snapshotPageProxyClass;
    }

    /**
     * {@inheritdoc}
     */
    public function create(SnapshotManagerInterface $manager, TransformerInterface $transformer, SnapshotInterface $snapshot)
    {
        return new $this->snapshotPageProxyClass($manager, $transformer, $snapshot);
    }
}
