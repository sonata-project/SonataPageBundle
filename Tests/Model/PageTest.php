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

class PageTest extends \PHPUnit_Framework_TestCase
{
    public function testSlugify()
    {
        $this->assertEquals(Page::slugify('test'), 'test');
        $this->assertEquals(Page::slugify('S§!@@#$#$alut'), 's-alut');
        $this->assertEquals(Page::slugify('Symfony2'), 'symfony2');
        $this->assertEquals(Page::slugify('test'), 'test');
    }

    public function testHeader()
    {
        $headers = array(
            'Location' => 'http://www.google.fr',
            'Expires' => '0',
        );

        $page = new Page;
        $page->setRawHeaders("Location: http://www.google.fr\r\nExpires: 0");
        $this->assertEquals($page->getHeaders(), $headers);

        $headers['Cache-Control'] = 'no-cache';
        $page->addHeader('Cache-Control', "no-cache");
        $this->assertEquals($page->getHeaders(), $headers);

        $page->addHeader('Location', "http://www.google.com");
        $headers['Location'] = 'http://www.google.com';
        $this->assertEquals($page->getHeaders(), $headers);
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
}