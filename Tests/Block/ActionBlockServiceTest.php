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

use Sonata\PageBundle\Tests\Model\Block;
use Sonata\PageBundle\Tests\Model\Page;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Form;
use Sonata\PageBundle\Block\ActionBlockService;

class ActionBlockServiceTest extends BaseTestBlockService
{
    public function testService()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface', array('render', 'handle'));

        $kernel->expects($this->exactly(1))
            ->method('render');

        $templating = new FakeTemplating;
        $service = new ActionBlockService('sonata.page.block.action', $templating, $kernel);

        $block = new Block;
        $block->setType('core.action');
        $block->setSettings(array(
            'action' => 'SonataPageBundle:Page:blockPreview'
        ));

        $manager = $this->getMock('Sonata\\PageBundle\\CmsManager\\CmsManagerInterface');

        $formMapper = $this->getMock('Sonata\\AdminBundle\\Form\\FormMapper', array(), array(), '', false);
        $formMapper->expects($this->exactly(2))
            ->method('add');

        $service->buildCreateForm($manager, $formMapper, $block);
        $service->buildEditForm($manager, $formMapper, $block);

        $page = new Page;

        $service->execute($manager, $block, $page);

        $this->assertEquals('SonataPageBundle:Page:blockPreview', $templating->parameters['block']->getSetting('action'));
    }
}