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

class ActionBlockServiceTest extends BaseTestBlockService
{

    public function testService()
    {
        $container = new Container;

        $service = new ContainerBlockService('core.action', $container);

        $this->assertEquals('SonataPageBundle:BlockAdmin:block_core_action_edit.html.twig', $service->getEditTemplate());
        $this->assertEquals('SonataPageBundle:Block:block_core_action.html.twig', $service->getViewTemplate());


        $block = new Block;
        $block->setType('core.action');
        $block->setSettings(array(
            'action' => 'SonataPageBundle:Page:blockPreview'
        ));

        $field = new FieldGroup('form');
        $service->defineBlockGroupField($field, $block);

        $this->assertEquals(0, count($field->getFields()));

//        $page = new Page;
//
//        $templating = new FakeTemplating;
//
//        $container->set('templating', $templating);
//
//        $service->execute($block, $page);
//
//        $this->assertEquals('my text', $templating->params['content']);
    }
}