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

    protected $blockInteractor;

    protected $snapshotManager;

    protected $transformer;

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
     * @expectedException \Sonata\PageBundle\Exception\PageNotFoundException
     */
    public function testGetPageWithUnknownPage()
    {
        $this->snapshotManager->expects($this->once())->method('findEnableSnapshot')->will($this->returnValue(null));

        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $snapshotManager = new CmsSnapshotManager($this->snapshotManager, $this->transformer);

        $snapshotManager->getPage($site, 1);
    }

    public function testGetPageWithId()
    {
        $cBlock = $this->getMock('Sonata\BlockBundle\Model\BlockInterface');
        $cBlock->expects($this->any())->method('hasChildren')->will($this->returnValue(false));
        $cBlock->expects($this->any())->method('getId')->will($this->returnValue(2));

        $pBlock = $this->getMock('Sonata\BlockBundle\Model\BlockInterface');
        $pBlock->expects($this->any())->method('getChildren')->will($this->returnValue(array($cBlock)));
        $pBlock->expects($this->any())->method('hasChildren')->will($this->returnValue(true));
        $pBlock->expects($this->any())->method('getId')->will($this->returnValue(1));

        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->any())->method('getBlocks')->will($this->returnCallback(function() use ($pBlock) {
            static $count;

            $count++;

            if ($count == 1) {
                return array();
            }

            return array($pBlock);
        }));

        $snapshot = $this->getMock('Sonata\PageBundle\Model\SnapshotInterface');
        $snapshot->expects($this->once())->method('getContent')->will($this->returnValue(array(
            // we don't care here about real values, the mock transformer will return the valid $pBlock instance
            'blocks' => array()
        )));

        $this->snapshotManager->expects($this->once())->method('findEnableSnapshot')->will($this->returnValue($snapshot));
        $this->transformer->expects($this->once())->method('load')->will($this->returnValue($page));

        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $snapshotManager = new CmsSnapshotManager($this->snapshotManager, $this->transformer);

        $page = $snapshotManager->getPage($site, 1);

        $this->assertInstanceOf('Sonata\PageBundle\Model\SnapshotPageProxy', $page);

        $this->assertInstanceOf('Sonata\BlockBundle\Model\BlockInterface', $snapshotManager->getBlock(1));
        $this->assertInstanceOf('Sonata\BlockBundle\Model\BlockInterface', $snapshotManager->getBlock(2));
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
