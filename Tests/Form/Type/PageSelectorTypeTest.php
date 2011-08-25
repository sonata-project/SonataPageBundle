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

use Sonata\AdminBundle\Model\ORM\ModelManager;
use Sonata\PageBundle\Form\Type\PageSelectorType;
use Sonata\PageBundle\Tests\Model\Page;

class PageSelectorTypeTest extends \PHPUnit_Framework_TestCase
{
    public function loadPages()
    {
        $pages = array();

        $i = 1;

        $pageAll = new Page();
        $pageAll->setId($i);
        $pageAll->setRequestMethod('');
        $pageAll->setRouteName('all');
        $pageAll->setUrl('/all');
        $pages[$i++] = $pageAll;

        $pagePost = new Page();
        $pagePost->setId($i);
        $pagePost->setRequestMethod('POST');
        $pagePost->setRouteName('post');
        $pagePost->setUrl('/post');
        $pagePost->setParent($pageAll);;
        $pages[$i++] = $pagePost;

        $pageGet = new Page();
        $pageGet->setId($i);
        $pageGet->setRequestMethod('GET');
        $pageGet->setRouteName('get');
        $pageGet->setUrl('/get');
        $pageGet->setParent($pageAll);
        $pages[$i++] = $pageGet;

        $page = new Page();
        $page->setId($i);
        $page->setRequestMethod('GET|POST');
        $page->setRouteName('get-post');
        $page->setUrl('/get-post');
        $page->setParent($pageAll);
        $pages[$i++] = $page;

        return $pages;
    }

    public function getPageManager()
    {
        $manager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');

        $manager->expects($this->any())
            ->method('loadPages')
            ->will($this->returnValue($this->loadPages()));

        return $manager;
    }

    public function testAllRequestMethodChoices()
    {
        $pageSelector = new  PageSelectorType($this->getPageManager());

        $options = $pageSelector->getDefaultOptions(array('choice_list' => 'fake', 'filter_choice' => array(
            'request_method' => 'all'
        )));

        $pages = $this->loadPages();

        $this->assertEquals($options['choices'], $pages);
    }
//
    public function testGetRequestMethodChoices()
    {
        $pageSelector = new  PageSelectorType($this->getPageManager());

        $options = $pageSelector->getDefaultOptions(array('choice_list' => 'fake'));

        $pages = $this->loadPages();
        unset($pages[2]);

        $this->assertEquals($options['choices'], $pages);
    }

    public function testPostRequestMethodChoices()
    {
        $pageSelector = new  PageSelectorType($this->getPageManager());

        $options = $pageSelector->getDefaultOptions(array('choice_list' => 'fake', 'filter_choice' => array(
            'request_method' => 'post'
        )));

        $pages = $this->loadPages();
        unset($pages[3]);

        $this->assertEquals($options['choices'], $pages);
    }

    public function testRootHierarchyChoices()
    {
        $pageSelector = new  PageSelectorType($this->getPageManager());

        $options = $pageSelector->getDefaultOptions(array('choice_list' => 'fake', 'filter_choice' => array(
            'hierarchy' => 'root',
            'request_method' => 'all'
        )));

        $pageTmps = $this->loadPages();
        $pages = array(1 => $pageTmps[1]);

        $this->assertEquals($options['choices'], $pages);
    }

    public function testChildrenHierarchyChoices()
    {
        $pageSelector = new  PageSelectorType($this->getPageManager());

        $options = $pageSelector->getDefaultOptions(array('choice_list' => 'fake', 'filter_choice' => array(
            'hierarchy' => 'children',
            'request_method' => 'all'
        )));

        $pages = $this->loadPages();
        unset($pages[1]);

        $this->assertEquals($options['choices'], $pages);
    }


    public function testComplexHierarchyChoices()
    {
        $pageSelector = new  PageSelectorType($this->getPageManager());

        $options = $pageSelector->getDefaultOptions(array('choice_list' => 'fake', 'filter_choice' => array(
            'hierarchy' => 'children',
            'request_method' => 'POST'
        )));

        $pages = $this->loadPages();
        unset($pages[1], $pages[3]);

        $this->assertEquals($options['choices'], $pages);
    }

}