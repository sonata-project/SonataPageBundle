<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Page;

use Sonata\PageBundle\CmsManager\CmsSnapshotManager;
use Sonata\PageBundle\Model\Block;
use Sonata\PageBundle\Tests\Model\Page;
use Sonata\PageBundle\Model\BlockInteractorInterface;

class SnapshotBlock extends Block
{
    public function setId($id)
    {}

    public function getId()
    {}
}

/**
 * Test CmsSnapshotManager
 */
class CmsSnapshotManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Sonata\PageBundle\CmsManager\CmsSnapshotManager
     */
    protected $manager;

    /**
     * Setup manager object to test
     */
    public function setUp()
    {
        $this->blockInteractor = $this->getMockBlockInteractor();
        $this->snapshotManager  = $this->getMock('Sonata\PageBundle\Model\SnapshotManagerInterface');
        $this->transformer  = $this->getMock('Sonata\PageBundle\Model\TransformerInterface');
        $this->manager = new CmsSnapshotManager($this->snapshotManager, $this->transformer);
    }

    /**
     * Test finding an existing container in a page
     */
    public function testFindExistingContainer()
    {
        $block = new SnapshotBlock();
        $block->setSettings(array('code' => 'findme'));

        $page = new Page();
        $page->addBlocks($block);

        $container = $this->manager->findContainer('findme', $page);

        $this->assertEquals(spl_object_hash($block), spl_object_hash($container), 'should retrieve the block of the page');
    }

    /**
     * Test finding an non-existing container in a page does NOT create a new block
     */
    public function testFindNonExistingContainerCreatesNoNewBlock()
    {
        $page = new Page();

        $container = $this->manager->findContainer('newcontainer', $page);

        $this->assertNull($container, 'should not create a new container block');
    }

    /**
     * Returns a mock block interactor
     *
     * @return \Sonata\PageBundle\Model\BlockInteractorInterface
     */
    protected function getMockBlockInteractor()
    {
        $callback = function($options) {
            $block = new SnapshotBlock;
            $block->setSettings($options);

            return $block;
        };

        $mock = $this->getMock('Sonata\PageBundle\Model\BlockInteractorInterface');
        $mock->expects($this->any())->method('createNewContainer')->will($this->returnCallback($callback));

        return $mock;
    }
}
