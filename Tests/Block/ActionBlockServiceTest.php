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
use Sonata\PageBundle\Block\ActionBlockService;

class ActionBlockServiceTest extends BaseTestBlockService
{

    public function testService()
    {

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $service = new ActionBlockService('sonata.page.block.action', new FakeTemplating, $kernel);

        $block = new Block;
        $block->setType('core.action');
        $block->setSettings(array(
            'action' => 'SonataPage:Page:blockPreview'
        ));

        $field = new Form('form');
        $service->defineBlockForm($field, $block);

        $this->assertEquals(2, count($field->getFields()));

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