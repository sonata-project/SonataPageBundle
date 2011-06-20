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
use Sonata\PageBundle\Block\TextBlockService;

class TextBlockServiceTest extends BaseTestBlockService
{

    public function testService()
    {
        $templating = new FakeTemplating;
        $service    = new TextBlockService('sonata.page.block.text', $templating);
        $page       = new Page;

        $block = new Block;
        $block->setType('core.text');
        $block->setSettings(array(
            'content' => 'my text'
        ));

        $formMapper = $this->getMock('Sonata\\AdminBundle\\Form\\FormMapper', array(), array(), '', false);
        $formMapper->expects($this->exactly(2))
            ->method('addType');

        $service->buildCreateForm($formMapper, $block);
        $service->buildEditForm($formMapper, $block);

        $response = $service->execute($block, $page);

        $this->assertEquals('my text', $templating->parameters['settings']['content']);
    }
}