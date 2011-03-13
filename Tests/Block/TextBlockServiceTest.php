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
use Sonata\PageBundle\Block\TextBlockService;

class TextBlockServiceTest extends BaseTestBlockService
{

    public function testService()
    {
        $templating = new FakeTemplating;

        $service = new TextBlockService('sonata.page.block.text', $templating);

        $block = new Block;
        $block->setType('core.text');
        $block->setSettings(array(
            'content' => 'my text'
        ));

        $field = new Form('form');
        $service->defineBlockForm($field, $block);

        $this->assertEquals(1, count($field->getFields()));

        $page = new Page;

        $service->execute($block, $page);

        $this->assertEquals('my text', $templating->parameters['content']);
    }
}