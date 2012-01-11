<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Form\Type;

use Sonata\PageBundle\Form\Type\PageSelectorType;
use Sonata\PageBundle\Tests\Model\Page;
use Sonata\PageBundle\Tests\Model\Site;

class PageSelectorTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $pages;

    protected $site;

    public function setUp()
    {
        $pages = array();

        $i = 1;

        $this->site = new Site;

        $pageAll = new Page();
        $pageAll->setId($i);
        $pageAll->setRequestMethod('');
        $pageAll->setRouteName('all');
        $pageAll->setUrl('/all');
        $pageAll->setSite($this->site);
        $pages[$i++] = $pageAll;

        $pagePost = new Page();
        $pagePost->setId($i);
        $pagePost->setRequestMethod('POST');
        $pagePost->setRouteName('post');
        $pagePost->setUrl('/post');
        $pagePost->setParent($pageAll);
        $pagePost->setSite($this->site);
        $pages[$i++] = $pagePost;

        $pageGet = new Page();
        $pageGet->setId($i);
        $pageGet->setRequestMethod('GET');
        $pageGet->setRouteName('get');
        $pageGet->setUrl('/get');
        $pageGet->setParent($pageAll);
        $pageGet->setSite($this->site);
        $pages[$i++] = $pageGet;

        $page = new Page();
        $page->setId($i);
        $page->setRequestMethod('GET|POST');
        $page->setRouteName('get-post');
        $page->setUrl('/get-post');
        $page->setParent($pageAll);
        $page->setSite($this->site);
        $pages[$i++] = $page;

        $this->pages = $pages;
    }

    public function getPageManager()
    {
        $manager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');

        $manager->expects($this->any())
            ->method('loadPages')
            ->will($this->returnValue($this->pages));

        return $manager;
    }

    public function testNoSite()
    {
        $pageSelector = new  PageSelectorType($this->getPageManager());

        $options = $pageSelector->getDefaultOptions(array('choice_list' => 'fake', 'filter_choice' => array(
            'request_method' => 'all'
        )));

        $this->assertEquals(array(), $options['choices']);
    }


    public function testAllRequestMethodChoices()
    {
        $pageSelector = new  PageSelectorType($this->getPageManager());

        $options = $pageSelector->getDefaultOptions(array('site' => $this->site, 'choice_list' => 'fake', 'filter_choice' => array(
            'request_method' => 'all'
        )));

        $this->assertEquals($this->pages, $options['choices']);
    }


    public function testGetRequestMethodChoices()
    {
        $pageSelector = new  PageSelectorType($this->getPageManager());

        $options = $pageSelector->getDefaultOptions(array('site' => $this->site, 'choice_list' => 'fake'));

        unset($this->pages[2]);

        $this->assertEquals($this->pages, $options['choices']);
    }

    public function testPostRequestMethodChoices()
    {
        $pageSelector = new  PageSelectorType($this->getPageManager());

        $options = $pageSelector->getDefaultOptions(array('site' => $this->site, 'choice_list' => 'fake', 'filter_choice' => array(
            'request_method' => 'post'
        )));

        unset($this->pages[3]);

        $this->assertEquals($this->pages, $options['choices']);
    }

    public function testRootHierarchyChoices()
    {
        $pageSelector = new  PageSelectorType($this->getPageManager());

        $options = $pageSelector->getDefaultOptions(array('site' => $this->site, 'choice_list' => 'fake', 'filter_choice' => array(
            'hierarchy' => 'root',
            'request_method' => 'all'
        )));

        $this->assertEquals(array(1 => $this->pages[1]), $options['choices']);
    }

    public function testChildrenHierarchyChoices()
    {
        $pageSelector = new  PageSelectorType($this->getPageManager());

        $options = $pageSelector->getDefaultOptions(array('site' => $this->site, 'choice_list' => 'fake', 'filter_choice' => array(
            'hierarchy' => 'children',
            'request_method' => 'all'
        )));

        unset($this->pages[1]);

        $this->assertEquals($this->pages, $options['choices']);
    }

    public function testComplexHierarchyChoices()
    {
        $pageSelector = new  PageSelectorType($this->getPageManager());

        $options = $pageSelector->getDefaultOptions(array('site' => $this->site, 'choice_list' => 'fake', 'filter_choice' => array(
            'hierarchy' => 'children',
            'request_method' => 'POST'
        )));

        unset($this->pages[1]);
        unset($this->pages[3]);

        $this->assertEquals($this->pages, $options['choices']);
    }
}