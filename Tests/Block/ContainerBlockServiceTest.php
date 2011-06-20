<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Block;

use Sonata\PageBundle\Tests\Entity\Block;
use Sonata\PageBundle\Tests\Entity\Page;
use Sonata\PageBundle\Block\ContainerBlockService;

class ContainerBlockServiceTest extends BaseTestBlockService
{
    public function testService()
    {
        $templating =  new FakeTemplating;
        $service = new ContainerBlockService('core.container', $templating);

        $block = new Block;
        $block->setType('core.container');
        $block->setSettings(array(
            'name' => 'Symfony'
        ));
        $formMapper = $this->getMock('Sonata\\AdminBundle\\Form\\FormMapper', array(), array(), '', false);
        $formMapper->expects($this->exactly(2))
            ->method('addType');
        $formMapper->expects($this->exactly(4))
            ->method('add');

        $service->buildCreateForm($formMapper, $block);
        $service->buildEditForm($formMapper, $block);

        $page = new Page;

        $service->execute($block, $page);

        $this->assertEquals('SonataPageBundle:Block:block_container.html.twig', $templating->view);
        $this->assertEquals('Symfony', $templating->parameters['container']->getSetting('name'));
        $this->assertInstanceOf('Sonata\PageBundle\Tests\Entity\Page', $templating->parameters['page']);
        $this->assertInstanceOf('Sonata\PageBundle\Tests\Entity\Block', $templating->parameters['container']);
    }
}