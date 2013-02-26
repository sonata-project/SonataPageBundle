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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 */
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
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->setDefaultOptions($options = new OptionsResolver());

        $options = $options->resolve(array('filter_choice' => array(
            'request_method' => 'all'
        )));

        $this->assertInstanceOf('Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList', $options['choice_list']);
        $this->assertEquals(array(), $options['choice_list']->getValues());
    }

    public function testAllRequestMethodChoices()
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->setDefaultOptions($options = new OptionsResolver());

        $options = $options->resolve(array('site' => $this->site, 'filter_choice' => array(
            'request_method' => 'all'
        )));

        $this->assertInstanceOf('Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList', $options['choice_list']);
        $this->assertCount(4, $options['choice_list']->getRemainingViews());
    }

    public function testGetRequestMethodChoices()
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->setDefaultOptions($options = new OptionsResolver());

        $options = $options->resolve(array('site' => $this->site));

        $this->assertInstanceOf('Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList', $options['choice_list']);
        $views = $options['choice_list']->getRemainingViews();

        $this->assertCount(3, $views);
        $this->assertEquals('all', $views[0]->label->getRouteName());
        $this->assertEquals('get', $views[1]->label->getRouteName());
        $this->assertEquals('get-post', $views[2]->label->getRouteName());
    }

    public function testPostRequestMethodChoices()
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->setDefaultOptions($options = new OptionsResolver());

        $options = $options->resolve(array('site' => $this->site, 'filter_choice' => array(
            'request_method' => 'post'
        )));

        $this->assertInstanceOf('Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList', $options['choice_list']);
        $views = $options['choice_list']->getRemainingViews();

        $this->assertCount(3, $views);
        $this->assertEquals('all', $views[0]->label->getRouteName());
        $this->assertEquals('post', $views[1]->label->getRouteName());
        $this->assertEquals('get-post', $views[2]->label->getRouteName());
    }

    public function testRootHierarchyChoices()
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->setDefaultOptions($options = new OptionsResolver());

        $options = $options->resolve(array('site' => $this->site, 'filter_choice' => array(
            'hierarchy'      => 'root',
            'request_method' => 'all'
        )));

        $this->assertInstanceOf('Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList', $options['choice_list']);
        $views = $options['choice_list']->getRemainingViews();

        $this->assertCount(1, $views);
        $this->assertEquals('all', $views[0]->label->getRouteName());
    }

    public function testChildrenHierarchyChoices()
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->setDefaultOptions($options = new OptionsResolver());

        $options = $options->resolve(array('site' => $this->site, 'filter_choice' => array(
            'hierarchy'      => 'children',
            'request_method' => 'all'
        )));

        $this->assertInstanceOf('Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList', $options['choice_list']);
        $views = $options['choice_list']->getRemainingViews();

        $this->assertCount(3, $views);
        $this->assertEquals('post', $views[0]->label->getRouteName());
        $this->assertEquals('get', $views[1]->label->getRouteName());
        $this->assertEquals('get-post', $views[2]->label->getRouteName());
    }

    public function testComplexHierarchyChoices()
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->setDefaultOptions($options = new OptionsResolver());

        $options = $options->resolve(array('site' => $this->site, 'filter_choice' => array(
            'hierarchy' => 'children',
            'request_method' => 'POST'
        )));

        $this->assertInstanceOf('Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList', $options['choice_list']);
        $views = $options['choice_list']->getRemainingViews();

        $this->assertCount(2, $views);
        $this->assertEquals('post', $views[0]->label->getRouteName());
        $this->assertEquals('get-post', $views[1]->label->getRouteName());
    }
}
