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

final class PageTest extends TestCase
{
    public function testSlugify(): void
    {
        setlocale(\LC_ALL, 'en_US.utf8');

        $reflectionClass = new \ReflectionClass(Page::class);
        $property = $reflectionClass->getProperty('slugifyMethod');
        $property->setAccessible(true);
        $property->setValue(null);

        static::assertSame(Page::slugify('test'), 'test');
        static::assertSame(Page::slugify('S§!@@#$#$alut'), 's-alut');
        static::assertSame(Page::slugify('Symfony2'), 'symfony2');
        static::assertSame(Page::slugify('test'), 'test');
        static::assertSame(Page::slugify('c\'est bientôt l\'été'), 'c-est-bientot-l-ete');
        static::assertSame(Page::slugify(urldecode('%2Fc\'est+bientôt+l\'été')), 'c-est-bientot-l-ete');
    }

    public function testHeader(): void
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
            static::assertSame(
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
            static::assertSame(
                $expectedStringHeaders,
                $method->invokeArgs($page, [$headers]),
                'Page::getHeadersAsString()'
            );
        }

        $page = new Page();
        $page->setHeaders($expectedHeaders);
        static::assertSame($page->getRawHeaders(), $expectedStringHeaders);
        static::assertSame($page->getHeaders(), $expectedHeaders);

        $page->setHeaders(['Cache-Control' => 'no-cache']);
        static::assertSame($page->getRawHeaders(), 'Cache-Control: no-cache');
        static::assertSame($page->getHeaders(), ['Cache-Control' => 'no-cache']);

        $page->setHeaders([]);
        static::assertNull($page->getRawHeaders());
        static::assertSame($page->getHeaders(), []);

        $page = new Page();
        $page->setRawHeaders($expectedStringHeaders);
        static::assertSame($page->getRawHeaders(), $expectedStringHeaders);
        static::assertSame($page->getHeaders(), $expectedHeaders);

        $page->setRawHeaders('Cache-Control: no-cache');
        static::assertSame($page->getRawHeaders(), 'Cache-Control: no-cache');
        static::assertSame($page->getHeaders(), ['Cache-Control' => 'no-cache']);

        $page->setRawHeaders('');
        static::assertNull($page->getRawHeaders());
        static::assertSame($page->getHeaders(), []);

        $page = new Page();
        $page->addHeader('Cache-Control', 'no-cache');
        static::assertSame($page->getRawHeaders(), 'Cache-Control: no-cache');
        static::assertSame($page->getHeaders(), ['Cache-Control' => 'no-cache']);

        $page->setRawHeaders($expectedStringHeaders);
        static::assertSame($page->getRawHeaders(), $expectedStringHeaders);
        static::assertSame($page->getHeaders(), $expectedHeaders);

        $page->addHeader('Cache-Control', 'no-cache, private');
        static::assertSame(
            $page->getRawHeaders(),
            $expectedStringHeaders."\r\nCache-Control: no-cache, private"
        );
        static::assertSame(
            $page->getHeaders(),
            array_merge($expectedHeaders, ['Cache-Control' => 'no-cache, private'])
        );

        $page->setRawHeaders($expectedStringHeaders);
        static::assertSame($page->getRawHeaders(), $expectedStringHeaders);
        static::assertSame($page->getHeaders(), $expectedHeaders);

        $page->addHeader('Location', 'http://www.google.com');
        $expectedHeaders['Location'] = 'http://www.google.com';
        static::assertSame($page->getHeaders(), $expectedHeaders);
    }

    public function testHasRequestMethod(): void
    {
        $page = new Page();
        $page->setRequestMethod('POST');
        static::assertTrue($page->hasRequestMethod('POST'));
        static::assertFalse($page->hasRequestMethod('GeT'));

        $page->setRequestMethod('POST|GET');
        static::assertTrue($page->hasRequestMethod('POsT'));
        static::assertTrue($page->hasRequestMethod('GET'));

        $page->setRequestMethod('');
        static::assertTrue($page->hasRequestMethod('GET'));
        static::assertTrue($page->hasRequestMethod('post'));
        static::assertFalse($page->hasRequestMethod('biloute'));
    }

    public function testGetterSetter(): void
    {
        $page = new Page();
        $page->setEnabled(true);
        static::assertTrue($page->getEnabled());

        $page->setCustomUrl('http://foo.bar');
        static::assertSame('http://foo.bar', $page->getCustomUrl());

        $page->setMetaKeyword('foo, bar');
        static::assertSame('foo, bar', $page->getMetaKeyword());

        $page->setMetaDescription('Foo bar is awesome');
        static::assertSame('Foo bar is awesome', $page->getMetaDescription());

        $page->setJavascript("alert('foo bar is around')");
        static::assertSame("alert('foo bar is around')", $page->getJavascript());

        $page->setStylesheet('foo.bar { display: block; }');
        static::assertSame('foo.bar { display: block; }', $page->getStylesheet());

        $time = new \DateTime();
        $page->setCreatedAt($time);
        $page->setUpdatedAt($time);
        static::assertSame($time, $page->getCreatedAt());
        static::assertSame($time, $page->getUpdatedAt());

        $children = [
            new Page(),
            new Page(),
        ];

        $page->setChildren($children);
        static::assertCount(2, $page->getChildren());

        $snapshots = [
            $this->createMock(SnapshotInterface::class),
        ];

        $page->setSnapshots($snapshots);
        static::assertCount(1, $page->getSnapshots());
        $page->addSnapshot($this->createMock(SnapshotInterface::class));
        static::assertCount(2, $page->getSnapshots());

        static::assertInstanceOf(SnapshotInterface::class, $page->getSnapshot());

        $page->setTarget($this->createMock(PageInterface::class));
        static::assertInstanceOf(PageInterface::class, $page->getTarget());
        $page->setTarget(null);
        static::assertNull($page->getTarget());

        $page->setTemplateCode('template1');
        static::assertSame('template1', $page->getTemplateCode());

        $page->setDecorate(true);
        static::assertTrue($page->getDecorate());

        $page->setPosition(1);
        static::assertSame(1, $page->getPosition());

        $page->setName(null);
        static::assertSame('-', (string) $page);
        $page->setName('Salut');
        static::assertSame('Salut', (string) $page);
    }

    public function testParents(): void
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
        static::assertSame('level 2', $parent->getName());
        $parent = $page->getParent(0);
        static::assertSame('root', $parent->getName());

        $parent = $page->getParent(1);
        static::assertSame('level 1', $parent->getName());
    }

    public function testPageTypeCMS(): void
    {
        $page = new Page();
        $page->setRouteName(Page::PAGE_ROUTE_CMS_NAME);

        static::assertTrue($page->isCms(), 'isCms');
        static::assertFalse($page->isDynamic(), 'isDynamic');
        static::assertFalse($page->isHybrid(), 'isHybrid');
        static::assertFalse($page->isInternal(), 'isInternal');
        static::assertFalse($page->isError(), 'isError');
    }

    public function testPageTypeHybrid(): void
    {
        $page = new Page();
        $page->setRouteName('foo_bar');
        $page->setUrl('/hello/thomas');

        static::assertFalse($page->isCms(), 'isCms');
        static::assertFalse($page->isDynamic(), 'isDynamic');
        static::assertTrue($page->isHybrid(), 'isHybrid');
        static::assertFalse($page->isInternal(), 'isInternal');
        static::assertFalse($page->isError(), 'isError');
    }

    public function testPageTypeInternal(): void
    {
        $page = new Page();
        $page->setName('global');
        $page->setRouteName('_page_internal_global');

        static::assertFalse($page->isCms(), 'isCms');
        static::assertFalse($page->isDynamic(), 'isDynamic');
        static::assertFalse($page->isHybrid(), 'isHybrid');
        static::assertTrue($page->isInternal(), 'isInternal');
        static::assertFalse($page->isError(), 'isError');
    }

    public function testPageTypeError(): void
    {
        $page = new Page();
        $page->setName('global');
        $page->setRouteName('_page_internal_error_global');

        static::assertFalse($page->isCms(), 'isCms');
        static::assertFalse($page->isDynamic(), 'isDynamic');
        static::assertFalse($page->isHybrid(), 'isHybrid');
        static::assertTrue($page->isInternal(), 'isInternal');
        static::assertTrue($page->isError(), 'isError');
    }

    public function testPageTypeDynamic(): void
    {
        $page = new Page();
        $page->setRouteName('foo_bar');
        $page->setUrl('/hello/{name}');

        static::assertFalse($page->isCms(), 'isCms');
        static::assertTrue($page->isDynamic(), 'isDynamic');
        static::assertTrue($page->isHybrid(), 'isHybrid');
        static::assertFalse($page->isInternal(), 'isInternal');
    }

    public function testGetContainer(): void
    {
        $page = new Page();

        $block1 = $this->createMock(Block::class);
        $block1->method('getType')->willReturn('sonata.page.block.action');

        $block2 = $this->createMock(Block::class);
        $block2->method('getType')->willReturn('sonata.page.block.container');
        $block2->expects(static::once())->method('getSetting')->willReturn('bar');

        $block3 = $this->createMock(Block::class);
        $block3->method('getType')->willReturn('sonata.page.block.container');
        $block3->expects(static::once())->method('getSetting')->willReturn('gotcha');

        $page->addBlocks($block1);
        $page->addBlocks($block2);
        $page->addBlocks($block3);

        static::assertSame($block3, $page->getContainerByCode('gotcha'));
    }

    public function testGetBlockByType(): void
    {
        $page = new Page();

        $block1 = $this->createMock(Block::class);
        $block1->expects(static::once())->method('getType')->willReturn('sonata.page.block.action');

        $block2 = $this->createMock(Block::class);
        $block2->expects(static::once())->method('getType')->willReturn('sonata.page.block.container');

        $block3 = $this->createMock(Block::class);
        $block3->expects(static::once())->method('getType')->willReturn('sonata.page.block.action');

        $page->addBlocks($block1);
        $page->addBlocks($block2);
        $page->addBlocks($block3);

        $types = $page->getBlocksByType('sonata.page.block.action');
        static::assertCount(2, $types);
    }
}
