<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Model\Block;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotInterface;

class PageTest extends TestCase
{
    /**
     * NEXT_MAJOR: remove the legacy group from this test.
     *
     * @group legacy
     */
    public function testSlugify()
    {
        setlocale(LC_ALL, 'en_US.utf8');
        setlocale(LC_CTYPE, 'en_US.utf8');

        $this->assertSame(Page::slugify('test'), 'test');
        $this->assertSame(Page::slugify('S§!@@#$#$alut'), 's-alut');
        $this->assertSame(Page::slugify('Symfony2'), 'symfony2');
        $this->assertSame(Page::slugify('test'), 'test');
        $this->assertSame(Page::slugify('c\'est bientôt l\'été'), 'c-est-bientot-l-ete');
        $this->assertSame(Page::slugify(urldecode('%2Fc\'est+bientôt+l\'été')), 'c-est-bientot-l-ete');
    }

    public function testHeader()
    {
        $expectedHeaders = [
            'Location' => 'http://www.google.fr',
            'Expires' => '0',
        ];
        $expectedStringHeaders = "Location: http://www.google.fr\r\nExpires: 0";

        $page = new Page();
        $pageReflection = new \ReflectionClass($page);

        $method = $pageReflection->getMethod('getHeadersAsArray');
        $method->setAccessible(true);
        foreach ([
                "Location: http://www.google.fr\r\nExpires: 0",
                " Location: http://www.google.fr\r\nExpires: 0 ",
                "Location:http://www.google.fr\r\nExpires:0",
                "\r\nLocation: http://www.google.fr\r\nExpires: 0\r\nInvalid Header Line",
            ] as $rawHeaders) {
            $this->assertSame(
                $expectedHeaders,
                $method->invokeArgs($page, [$rawHeaders]),
                'Page::getHeadersAsArray()'
            );
        }

        $method = $pageReflection->getMethod('getHeadersAsString');
        $method->setAccessible(true);
        foreach ([
                [
                    'Location' => 'http://www.google.fr',
                    'Expires' => '0',
                ],
                [

                    ' Location ' => ' http://www.google.fr ',
                    "\r\nExpires " => " 0\r\n",
                ],
            ] as $headers) {
            $this->assertSame(
                $expectedStringHeaders,
                $method->invokeArgs($page, [$headers]),
                'Page::getHeadersAsString()'
            );
        }

        $page = new Page();
        $page->setHeaders($expectedHeaders);
        $this->assertSame($page->getRawHeaders(), $expectedStringHeaders);
        $this->assertSame($page->getHeaders(), $expectedHeaders);

        $page->setHeaders(['Cache-Control' => 'no-cache']);
        $this->assertSame($page->getRawHeaders(), 'Cache-Control: no-cache');
        $this->assertSame($page->getHeaders(), ['Cache-Control' => 'no-cache']);

        $page->setHeaders([]);
        $this->assertNull($page->getRawHeaders());
        $this->assertSame($page->getHeaders(), []);

        $page = new Page();
        $page->setRawHeaders($expectedStringHeaders);
        $this->assertSame($page->getRawHeaders(), $expectedStringHeaders);
        $this->assertSame($page->getHeaders(), $expectedHeaders);

        $page->setRawHeaders('Cache-Control: no-cache');
        $this->assertSame($page->getRawHeaders(), 'Cache-Control: no-cache');
        $this->assertSame($page->getHeaders(), ['Cache-Control' => 'no-cache']);

        $page->setRawHeaders('');
        $this->assertNull($page->getRawHeaders());
        $this->assertSame($page->getHeaders(), []);

        $page = new Page();
        $page->addHeader('Cache-Control', 'no-cache');
        $this->assertSame($page->getRawHeaders(), 'Cache-Control: no-cache');
        $this->assertSame($page->getHeaders(), ['Cache-Control' => 'no-cache']);

        $page->setRawHeaders($expectedStringHeaders);
        $this->assertSame($page->getRawHeaders(), $expectedStringHeaders);
        $this->assertSame($page->getHeaders(), $expectedHeaders);

        $page->addHeader('Cache-Control', 'no-cache, private');
        $this->assertSame(
            $page->getRawHeaders(),
            $expectedStringHeaders."\r\nCache-Control: no-cache, private"
        );
        $this->assertSame(
            $page->getHeaders(),
            array_merge($expectedHeaders, ['Cache-Control' => 'no-cache, private'])
        );

        $page->setRawHeaders($expectedStringHeaders);
        $this->assertSame($page->getRawHeaders(), $expectedStringHeaders);
        $this->assertSame($page->getHeaders(), $expectedHeaders);

        $page->addHeader('Location', 'http://www.google.com');
        $expectedHeaders['Location'] = 'http://www.google.com';
        $this->assertSame($page->getHeaders(), $expectedHeaders);
    }

    public function testHasRequestMethod()
    {
        $page = new Page();
        $page->setRequestMethod('POST');
        $this->assertTrue($page->hasRequestMethod('POST'));
        $this->assertFalse($page->hasRequestMethod('GeT'));

        $page->setRequestMethod('POST|GET');
        $this->assertTrue($page->hasRequestMethod('POsT'));
        $this->assertTrue($page->hasRequestMethod('GET'));

        $page->setRequestMethod('');
        $this->assertTrue($page->hasRequestMethod('GET'));
        $this->assertTrue($page->hasRequestMethod('post'));
        $this->assertFalse($page->hasRequestMethod('biloute'));
    }

    public function testGetterSetter()
    {
        $page = new Page();
        $page->setEnabled(true);
        $this->assertTrue($page->getEnabled());

        $page->setCustomUrl('http://foo.bar');
        $this->assertSame('http://foo.bar', $page->getCustomUrl());

        $page->setMetaKeyword('foo, bar');
        $this->assertSame('foo, bar', $page->getMetaKeyword());

        $page->setMetaDescription('Foo bar is awesome');
        $this->assertSame('Foo bar is awesome', $page->getMetaDescription());

        $page->setJavascript("alert('foo bar is around')");
        $this->assertSame("alert('foo bar is around')", $page->getJavascript());

        $page->setStylesheet('foo.bar { display: block; }');
        $this->assertSame('foo.bar { display: block; }', $page->getStylesheet());

        $time = new \DateTime();
        $page->setCreatedAt($time);
        $page->setUpdatedAt($time);
        $this->assertSame($time, $page->getCreatedAt());
        $this->assertSame($time, $page->getUpdatedAt());

        $children = [
            new Page(),
            new Page(),
        ];

        $page->setChildren($children);
        $this->assertCount(2, $page->getChildren());

        $snapshots = [
            $this->createMock(SnapshotInterface::class),
        ];

        $page->setSnapshots($snapshots);
        $this->assertCount(1, $page->getSnapshots());
        $page->addSnapshot($this->createMock(SnapshotInterface::class));
        $this->assertCount(2, $page->getSnapshots());

        $this->assertInstanceOf(SnapshotInterface::class, $page->getSnapshot());

        $page->setTarget($this->createMock(PageInterface::class));
        $this->assertInstanceOf(PageInterface::class, $page->getTarget());
        $page->setTarget(null);
        $this->assertNull($page->getTarget());

        $page->setTemplateCode('template1');
        $this->assertSame('template1', $page->getTemplateCode());

        $page->setDecorate(true);
        $this->assertTrue($page->getDecorate());

        $page->setPosition(1);
        $this->assertSame(1, $page->getPosition());

        $page->setName(null);
        $this->assertSame('-', (string) $page);
        $page->setName('Salut');
        $this->assertSame('Salut', (string) $page);
    }

    public function testParents()
    {
        $root = new Page();
        $root->setName('root');

        $level1 = new Page();
        $level1->setName('level 1');
        $level2 = new Page();
        $level2->setName('level 2');

        $page = new Page();
        $page->setName('page');

        $level1->setParent($root);
        $level2->setParent($level1);
        $page->setParent($level2);

        $parent = $page->getParent();
        $this->assertSame('level 2', $parent->getName());
        $parent = $page->getParent(0);
        $this->assertSame('root', $parent->getName());

        $parent = $page->getParent(1);
        $this->assertSame('level 1', $parent->getName());
    }

    public function testPageTypeCMS()
    {
        $page = new Page();
        $page->setRouteName(Page::PAGE_ROUTE_CMS_NAME);

        $this->assertTrue($page->isCms(), 'isCms');
        $this->assertFalse($page->isDynamic(), 'isDynamic');
        $this->assertFalse($page->isHybrid(), 'isHybrid');
        $this->assertFalse($page->isInternal(), 'isInternal');
        $this->assertFalse($page->isError(), 'isError');
    }

    public function testPageTypeHybrid()
    {
        $page = new Page();
        $page->setRouteName('foo_bar');
        $page->setUrl('/hello/thomas');

        $this->assertFalse($page->isCms(), 'isCms');
        $this->assertFalse($page->isDynamic(), 'isDynamic');
        $this->assertTrue($page->isHybrid(), 'isHybrid');
        $this->assertFalse($page->isInternal(), 'isInternal');
        $this->assertFalse($page->isError(), 'isError');
    }

    public function testPageTypeInternal()
    {
        $page = new Page();
        $page->setName('global');
        $page->setRouteName('_page_internal_global');

        $this->assertFalse($page->isCms(), 'isCms');
        $this->assertFalse($page->isDynamic(), 'isDynamic');
        $this->assertFalse($page->isHybrid(), 'isHybrid');
        $this->assertTrue($page->isInternal(), 'isInternal');
        $this->assertFalse($page->isError(), 'isError');
    }

    public function testPageTypeError()
    {
        $page = new Page();
        $page->setName('global');
        $page->setRouteName('_page_internal_error_global');

        $this->assertFalse($page->isCms(), 'isCms');
        $this->assertFalse($page->isDynamic(), 'isDynamic');
        $this->assertFalse($page->isHybrid(), 'isHybrid');
        $this->assertTrue($page->isInternal(), 'isInternal');
        $this->assertTrue($page->isError(), 'isError');
    }

    public function testPageTypeDynamic()
    {
        $page = new Page();
        $page->setRouteName('foo_bar');
        $page->setUrl('/hello/{name}');

        $this->assertFalse($page->isCms(), 'isCms');
        $this->assertTrue($page->isDynamic(), 'isDynamic');
        $this->assertTrue($page->isHybrid(), 'isHybrid');
        $this->assertFalse($page->isInternal(), 'isInternal');
    }

    public function testGetContainer()
    {
        $page = new Page();

        $block1 = $this->createMock(Block::class);
        $block1->expects($this->any())->method('getType')->willReturn('sonata.page.block.action');

        $block2 = $this->createMock(Block::class);
        $block2->expects($this->any())->method('getType')->willReturn('sonata.page.block.container');
        $block2->expects($this->once())->method('getSetting')->willReturn('bar');

        $block3 = $this->createMock(Block::class);
        $block3->expects($this->any())->method('getType')->willReturn('sonata.page.block.container');
        $block3->expects($this->once())->method('getSetting')->willReturn('gotcha');

        $page->addBlocks($block1);
        $page->addBlocks($block2);
        $page->addBlocks($block3);

        $this->assertSame($block3, $page->getContainerByCode('gotcha'));
    }

    public function testGetBlockByType()
    {
        $page = new Page();

        $block1 = $this->createMock(Block::class);
        $block1->expects($this->once())->method('getType')->willReturn('sonata.page.block.action');

        $block2 = $this->createMock(Block::class);
        $block2->expects($this->once())->method('getType')->willReturn('sonata.page.block.container');

        $block3 = $this->createMock(Block::class);
        $block3->expects($this->once())->method('getType')->willReturn('sonata.page.block.action');

        $page->addBlocks($block1);
        $page->addBlocks($block2);
        $page->addBlocks($block3);

        $types = $page->getBlocksByType('sonata.page.block.action');
        $this->assertCount(2, $types);
    }
}
