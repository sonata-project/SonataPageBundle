<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests;

use Cocur\Slugify\Slugify;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\SonataPageBundle;

class Page extends \Sonata\PageBundle\Model\Page
{
    /**
     * Returns the id.
     *
     * @return mixed
     */
    public function getId()
    {
        // TODO: Implement getId() method.
    }
}

class SonataPageBundleTest extends TestCase
{
    /**
     * @dataProvider getSlug
     */
    public function testBoot($text, $expected)
    {
        $bundle = new SonataPageBundle();
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->exactly(1))->method('hasParameter')->will($this->returnValue(true));
        $container->expects($this->exactly(2))->method('getParameter')->will($this->returnCallback(function ($value) {
            if ('sonata.page.page.class' == $value) {
                return 'Sonata\PageBundle\Tests\Page';
            }

            if ('sonata.page.slugify_service' == $value) {
                return 'slug_service';
            }
        }));
        $container->expects($this->once())->method('get')->will($this->returnValue(Slugify::create()));

        $bundle->setContainer($container);
        $bundle->boot();

        $page = new Page();
        $page->setSlug($text);
        $this->assertEquals($page->getSlug(), $expected);
    }

    public function getSlug()
    {
        return [
            ['Salut comment ca va ?',  'salut-comment-ca-va'],
            ['òüì',  'ouei'],
        ];
    }
}
