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
use Symfony\Component\Form\Form;
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

        $field = new Form('form');
        $service->defineBlockForm($field, $block);

        $this->assertEquals(0, count($field->getFields()));

        $page = new Page;

        $service->execute($block, $page);

        $this->assertEquals('SonataPageBundle:Page:renderContainer', $templating->view);
        $this->assertEquals('Symfony', $templating->parameters['attributes']['name']);
        $this->assertInstanceOf('Sonata\PageBundle\Tests\Page\Page', $templating->parameters['attributes']['page']);
        $this->assertInstanceOf('Sonata\PageBundle\Tests\Page\Block', $templating->parameters['attributes']['parent_container']);
    }
}