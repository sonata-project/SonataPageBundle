<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Entity;

use Sonata\PageBundle\Tests\Model\Page;

/**
 *
 */
class PageTest extends \PHPUnit_Framework_TestCase
{
    public function testSlugify()
    {
        $this->assertEquals(Page::slugify('test'), 'test');
        $this->assertEquals(Page::slugify('S§!@@#$#$alut'), 's-alut');
        $this->assertEquals(Page::slugify('Symfony2'), 'symfony2');
        $this->assertEquals(Page::slugify('test'), 'test');
        $this->assertEquals(Page::slugify('c\'est bientôt l\'été'), 'c-est-bientot-l-ete');
        $this->assertEquals(Page::slugify(urldecode('%2Fc\'est+bientôt+l\'été')), 'c-est-bientot-l-ete');
    }

    public function testHeader()
    {
        $headersArray = array(
            'Location' => 'http://www.google.fr',
            'Expires' => '0',
        );
        $headersString = "Location: http://www.google.fr\r\nExpires: 0";

        $page = new Page;
        $pageReflection = new \ReflectionClass($page);

        $method = $pageReflection->getMethod('getHeadersAsArray');
        $method->setAccessible(true);
        foreach (array(
                "Location: http://www.google.fr\r\nExpires: 0",
                " Location: http://www.google.fr\r\nExpires: 0 ",
                "Location:http://www.google.fr\r\nExpires:0",
                "\r\nLocation: http://www.google.fr\r\nExpires: 0\r\nInvalid Header Line",
            ) as $rawHeaders) {
                $this->assertEquals($method->invokeArgs($page, array($rawHeaders)), $headersArray, 'Page::getHeadersAsArray()');
        }


        $method = $pageReflection->getMethod('getHeadersAsString');
        $method->setAccessible(true);
        foreach (array(
                array(
                    "Location" => "http://www.google.fr",
                    "Expires" => "0",
                ),
                array(
                    " Location " => " http://www.google.fr ",
                    "\r\nExpires " => " 0\r\n",
                ),
            ) as $headers) {
                $this->assertEquals($method->invokeArgs($page, array($headers)), $headersString, 'Page::getHeadersAsString()');
        }

        $page = new Page;
        $page->setHeaders($headersArray);
        $this->assertEquals($page->getRawHeaders(), $headersString);
        $this->assertEquals($page->getHeaders(), $headersArray);

        $page->setHeaders(array('Cache-Control' => 'no-cache'));
        $this->assertEquals($page->getRawHeaders(), 'Cache-Control: no-cache');
        $this->assertEquals($page->getHeaders(), array('Cache-Control' => 'no-cache'));

        $page->setHeaders(array());
        $this->assertEquals($page->getRawHeaders(), '');
        $this->assertEquals($page->getHeaders(), array());

        $page = new Page;
        $page->setRawHeaders($headersString);
        $this->assertEquals($page->getRawHeaders(), $headersString);
        $this->assertEquals($page->getHeaders(), $headersArray);

        $page->setRawHeaders('Cache-Control: no-cache');
        $this->assertEquals($page->getRawHeaders(), 'Cache-Control: no-cache');
        $this->assertEquals($page->getHeaders(), array('Cache-Control' => 'no-cache'));

        $page->setRawHeaders('');
        $this->assertEquals($page->getRawHeaders(), '');
        $this->assertEquals($page->getHeaders(), array());

        $page = new Page;
        $page->addHeader('Cache-Control', 'no-cache');
        $this->assertEquals($page->getRawHeaders(), 'Cache-Control: no-cache');
        $this->assertEquals($page->getHeaders(), array('Cache-Control' => 'no-cache'));

        $page->setRawHeaders($headersString);
        $this->assertEquals($page->getRawHeaders(), $headersString);
        $this->assertEquals($page->getHeaders(), $headersArray);

        $page->addHeader('Cache-Control', 'no-cache, private');
        $this->assertEquals($page->getRawHeaders(), $headersString."\r\nCache-Control: no-cache, private");
        $this->assertEquals($page->getHeaders(), array_merge($headersArray, array('Cache-Control' => 'no-cache, private')));

        $page->setRawHeaders($headersString);
        $this->assertEquals($page->getRawHeaders(), $headersString);
        $this->assertEquals($page->getHeaders(), $headersArray);

        $page->addHeader('Location', "http://www.google.com");
        $headersArray['Location'] = 'http://www.google.com';
        $this->assertEquals($page->getHeaders(), $headersArray);
    }

    public function testHasRequestMethod()
    {
        $page = new Page;
        $page->setRequestMethod("POST");
        $this->assertEquals($page->hasRequestMethod("POST"), true);
        $this->assertEquals($page->hasRequestMethod("GeT"), false);

        $page->setRequestMethod("POST|GET");
        $this->assertEquals($page->hasRequestMethod("POsT"), true);
        $this->assertEquals($page->hasRequestMethod("GET"), true);

        $page->setRequestMethod("");
        $this->assertEquals($page->hasRequestMethod("GET"), true);
        $this->assertEquals($page->hasRequestMethod("post"), true);
        $this->assertEquals($page->hasRequestMethod("biloute"), false);
    }

    public function testGetterSetter()
    {
        $page = new Page;
        $page->setEnabled(true);
        $this->assertTrue($page->getEnabled());

        $page->setCustomUrl('http://foo.bar');
        $this->assertEquals('http://foo.bar', $page->getCustomUrl());

        $page->setMetaKeyword('foo, bar');
        $this->assertEquals('foo, bar', $page->getMetaKeyword());

        $page->setMetaDescription('Foo bar is awesome');
        $this->assertEquals('Foo bar is awesome', $page->getMetaDescription());

        $page->setJavascript("alert('foo bar is around')");
        $this->assertEquals("alert('foo bar is around')", $page->getJavascript());

        $page->setStylesheet('foo.bar { display: block; }');
        $this->assertEquals('foo.bar { display: block; }', $page->getStylesheet());

        $time = new \DateTime();
        $page->setCreatedAt($time);
        $page->setUpdatedAt($time);
        $this->assertEquals($time, $page->getCreatedAt());
        $this->assertEquals($time, $page->getUpdatedAt());

        $children = array(
            new Page(),
            new Page()
        );

        $page->setChildren($children);
        $this->assertEquals(2, count($page->getChildren()));

        $snapshots = array(
            $this->getMock('Sonata\PageBundle\Model\SnapshotInterface')
        );

        $page->setSnapshots($snapshots);
        $this->assertEquals(1, count($page->getSnapshots()));
        $page->addSnapshot($this->getMock('Sonata\PageBundle\Model\SnapshotInterface'));
        $this->assertEquals(2, count($page->getSnapshots()));

        $this->assertInstanceOf('Sonata\PageBundle\Model\SnapshotInterface', $page->getSnapshot());

        $page->setTarget($this->getMock('Sonata\PageBundle\Model\PageInterface'));
        $this->assertInstanceOf('Sonata\PageBundle\Model\PageInterface', $page->getTarget());
        $page->setTarget(null);
        $this->assertNull($page->getTarget());

        $page->setTemplateCode('template1');
        $this->assertEquals('template1', $page->getTemplateCode());

        $page->setDecorate(true);
        $this->assertTrue($page->getDecorate());

        $page->setPosition(1);
        $this->assertEquals(1, $page->getPosition());

        $page->setName(null);
        $this->assertEquals('-', (string) $page);
        $page->setName('Salut');
        $this->assertEquals('Salut', (string) $page);
    }

    public function testParents()
    {
        $root = new Page;
        $root->setName('root');

        $level1 = new Page;
        $level1->setName('level 1');
        $level2 = new Page;
        $level2->setName('level 2');

        $page = new Page;
        $page->setName('page');

        $level1->setParent($root);
        $level2->setParent($level1);
        $page->setParent($level2);

        $parent = $page->getParent();
        $this->assertEquals('level 2', $parent->getName());
        $parent = $page->getParent(0);
        $this->assertEquals('root', $parent->getName());

        $parent = $page->getParent(1);
        $this->assertEquals('level 1', $parent->getName());
    }

    public function testPageTypeCMS()
    {
        $page = new Page;
        $page->setRouteName(Page::PAGE_ROUTE_CMS_NAME);

        $this->assertTrue($page->isCms(), 'isCms');
        $this->assertFalse($page->isDynamic(), 'isDynamic');
        $this->assertFalse($page->isHybrid(), 'isHybrid');
        $this->assertFalse($page->isInternal(), 'isInternal');
    }

    public function testPageTypeHybrid()
    {
        $page = new Page;
        $page->setRouteName('foo_bar');
        $page->setUrl('/hello/thomas');

        $this->assertFalse($page->isCms(), 'isCms');
        $this->assertFalse($page->isDynamic(), 'isDynamic');
        $this->assertTrue($page->isHybrid(), 'isHybrid');
        $this->assertFalse($page->isInternal(), 'isInternal');
    }

    public function testPageTypeDynamic()
    {
        $page = new Page;
        $page->setRouteName('foo_bar');
        $page->setUrl('/hello/{name}');

        $this->assertFalse($page->isCms(), 'isCms');
        $this->assertTrue($page->isDynamic(), 'isDynamic');
        $this->assertTrue($page->isHybrid(), 'isHybrid');
        $this->assertFalse($page->isInternal(), 'isInternal');
    }

    public function testPageTtl()
    {
        $page = new Page;
        $block1 = $this->getMockBuilder('Sonata\PageBundle\Model\Block')->getMock();
        $block1->expects($this->once())->method('getTtl')->will($this->returnValue(100));
        $block2 = $this->getMockBuilder('Sonata\PageBundle\Model\Block')->getMock();
        $block2->expects($this->once())->method('getTtl')->will($this->returnValue(50));
        $block3 = $this->getMockBuilder('Sonata\PageBundle\Model\Block')->getMock();
        $block3->expects($this->once())->method('getTtl')->will($this->returnValue(65));

        $page->addBlocks($block1);
        $page->addBlocks($block2);
        $page->addBlocks($block3);

        $this->assertEquals(50, $page->getTtl());
    }

    public function testGetContainer()
    {
        $page = new Page;

        $block1 = $this->getMockBuilder('Sonata\PageBundle\Model\Block')->getMock();
        $block1->expects($this->once())->method('getType')->will($this->returnValue('sonata.page.block.action'));

        $block2 = $this->getMockBuilder('Sonata\PageBundle\Model\Block')->getMock();
        $block2->expects($this->once())->method('getType')->will($this->returnValue('sonata.page.block.container'));
        $block2->expects($this->once())->method('getSetting')->will($this->returnValue('bar'));

        $block3 = $this->getMockBuilder('Sonata\PageBundle\Model\Block')->getMock();
        $block3->expects($this->once())->method('getType')->will($this->returnValue('sonata.page.block.container'));
        $block3->expects($this->once())->method('getSetting')->will($this->returnValue('gotcha'));

        $page->addBlocks($block1);
        $page->addBlocks($block2);
        $page->addBlocks($block3);

        $this->assertEquals($block3, $page->getContainerByCode('gotcha'));
    }

    public function testGetBlockByType()
    {
        $page = new Page;

        $block1 = $this->getMockBuilder('Sonata\PageBundle\Model\Block')->getMock();
        $block1->expects($this->once())->method('getType')->will($this->returnValue('sonata.page.block.action'));

        $block2 = $this->getMockBuilder('Sonata\PageBundle\Model\Block')->getMock();
        $block2->expects($this->once())->method('getType')->will($this->returnValue('sonata.page.block.container'));

        $block3 = $this->getMockBuilder('Sonata\PageBundle\Model\Block')->getMock();
        $block3->expects($this->once())->method('getType')->will($this->returnValue('sonata.page.block.action'));

        $page->addBlocks($block1);
        $page->addBlocks($block2);
        $page->addBlocks($block3);

        $types = $page->getBlocksByType('sonata.page.block.action');
        $this->assertEquals(2, count($types));
    }
}
