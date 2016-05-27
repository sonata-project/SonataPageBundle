<?php

namespace Sonata\PageBundle\Model;

class SnapshotPageProxyFactory
{
    /*
     * @var string
     */
    public $snapShotPageProxyClass;

    /**
     * SnapshotPageProxyFactory constructor.
     *
     * @param string $snapShotPageProxyClass SnapShopPageProxy class name
     */
    public function __construct($snapShotPageProxyClass)
    {
        $this->snapShotPageProxyClass = $snapShotPageProxyClass;
    }

    /**
     * Create snapshot instance.
     *
     * @param SnapshotManagerInterface $manager
     * @param TransformerInterface     $transformer
     * @param SnapshotInterface        $snapshot
     *
     * @return SnapshotPageProxyInterface
     */
    public function create(SnapshotManagerInterface $manager, TransformerInterface $transformer, SnapshotInterface $snapshot)
    {
        return new $this->snapShotPageProxyClass($manager, $transformer, $snapshot);
    }
}
