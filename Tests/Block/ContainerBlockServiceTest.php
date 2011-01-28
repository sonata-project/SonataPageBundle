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

use Sonata\PageBundle\Tests\Page\Block;
use Sonata\PageBundle\Tests\Page\Page;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FieldGroup;
use Sonata\PageBundle\Block\ContainerBlockService;

class ContainerBlockServiceTest extends BaseTestBlockService
{

    public function testService()
    {
        $container = new Container;

        $service = new ContainerBlockService('core.container', $container);

        $this->assertEquals('SonataPageBundle:BlockAdmin:block_core_container_edit.twig.html', $service->getEditTemplate());
        $this->assertEquals('SonataPageBundle:Block:block_core_container.twig.html', $service->getViewTemplate());


        $block = new Block;
        $block->setType('core.container');
        $block->setSettings(array(
            'name' => 'Symfony'
        ));

        $field = new FieldGroup('form');
        $service->defineBlockGroupField($field, $block);

        $this->assertEquals(0, count($field->getFields()));

        $page = new Page;

        $templating = new FakeTemplating;

        $container->set('templating', $templating);

        $service->execute($block, $page);

        $this->assertEquals('SonataPageBundle:Page:renderContainer', $templating->template);
        $this->assertEquals('Symfony', $templating->params['attributes']['name']);
        $this->assertInstanceOf('Sonata\PageBundle\Tests\Page\Page', $templating->params['attributes']['page']);
        $this->assertInstanceOf('Sonata\PageBundle\Tests\Page\Block', $templating->params['attributes']['parent_container']);
    }
}