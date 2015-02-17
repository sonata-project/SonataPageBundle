<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests;

use Cocur\Slugify\Slugify;
use Sonata\PageBundle\SonataPageBundle;

class Page extends \Sonata\PageBundle\Model\Page {
    /**
     * Returns the id
     *
     * @return mixed
     */
    public function getId()
    {
        // TODO: Implement getId() method.
    }
}

class SonataPageBundleTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider getSlug
     */
    public function testBoot($text, $expected)
    {
        $bundle = new SonataPageBundle();
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->exactly(1))->method('hasParameter')->will($this->returnValue(true));
        $container->expects($this->exactly(2))->method('getParameter')->will($this->returnCallback(function($value) {
            if ($value == 'sonata.page.page.class') {
                return 'Sonata\PageBundle\Tests\Page';
            }

            if ($value == 'sonata.page.slugify_service') {
                return 'slug_service';
            }
        }));
        $container->expects($this->once())->method('get')->will($this->returnValue(Slugify::create()));

        $bundle->setContainer($container);
        $bundle->boot();

        $page = new Page;
        $page->setSlug($text);
        $this->assertEquals($page->getSlug(), $expected);
    }

    public function getSlug()
    {
        return array(
            array("Salut comment ca va ?",  'salut-comment-ca-va'),
            array("òüì",  'ouei')
        );
    }
}
