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

use Sonata\PageBundle\CmsManager\CmsPageManager;
use Sonata\PageBundle\Model\Block as AbtractBlock;
use Sonata\PageBundle\Tests\Model\Page;
use Sonata\PageBundle\Model\BlockInteractorInterface;

class CmsBlock extends AbtractBlock
{
    public function setId($id)
    {}

    public function getId()
    {}
}

/**
 * Test CmsPageManager
 */
class CmsPageManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Sonata\PageBundle\CmsManager\CmsPageManager
     */
    protected $manager;

    /**
     * Setup manager object to test
     */
    public function setUp()
    {
        $this->blockInteractor = $this->getMockBlockInteractor();
        $this->pageManager  = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');
        $this->manager = new CmsPageManager($this->pageManager, $this->blockInteractor);
    }

    /**
     * Test finding an existing container in a page
     */
    public function testFindExistingContainer()
    {
        $block = new CmsBlock();
        $block->setSettings(array('code' => 'findme'));

        $page = new Page();
        $page->addBlocks($block);

        $container = $this->manager->findContainer('findme', $page);

        $this->assertEquals(spl_object_hash($block), spl_object_hash($container),
            'should retrieve the block of the page');
    }

    /**
     * Test finding an non-existing container in a page does create a new block
     */
    public function testFindNonExistingContainerCreatesNewBlock()
    {
        $page = new Page();

        $container = $this->manager->findContainer('newcontainer', $page);

        $this->assertInstanceOf('Sonata\PageBundle\Model\PageBlockInterface', $container, 'should be a block');
        $this->assertEquals('newcontainer', $container->getSetting('code'));
    }

    /**
     * Returns a mock block interactor
     *
     * @return \Sonata\PageBundle\Model\BlockInteractorInterface
     */
    protected function getMockBlockInteractor()
    {
        $callback = function($options) {
            $block = new CmsBlock;
            $block->setSettings($options);

            return $block;
        };

        $mock = $this->getMock('Sonata\PageBundle\Model\BlockInteractorInterface');
        $mock->expects($this->any())->method('createNewContainer')->will($this->returnCallback($callback));

        return $mock;
    }
}
