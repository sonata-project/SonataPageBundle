<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Form\Type;

use Sonata\PageBundle\Form\Type\PageSelectorType;
use Sonata\PageBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Sonata\PageBundle\Tests\Model\Page;
use Sonata\PageBundle\Tests\Model\Site;
use Symfony\Component\Form\Extension\Core\View\ChoiceView as LegacyChoiceView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageSelectorTypeTest extends PHPUnit_Framework_TestCase
{
    protected $pages;

    protected $site;

    public function setUp()
    {
        $pages = array();

        $i = 1;

        $this->site = new Site();

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
        $manager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');

        $manager->expects($this->any())
            ->method('loadPages')
            ->will($this->returnValue($this->pages));

        return $manager;
    }

    /**
     * @group legacy
     */
    public function testNoSite()
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->configureOptions($options = new OptionsResolver());

        $options = $options->resolve(array('filter_choice' => array(
            'request_method' => 'all',
        )));

        $this->assertInstanceOf(
            class_exists('Symfony\Component\Form\ChoiceList\ArrayChoiceList') ? // NEXT_MAJOR: remove condition
                'Symfony\Component\Form\ChoiceList\ArrayChoiceList' :
                'Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList',
            $options['choice_list']
        );
        $this->assertEquals(array(), $options['choice_list']->getValues());
    }

    /**
     * @group legacy
     */
    public function testAllRequestMethodChoices()
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->configureOptions($options = new OptionsResolver());

        $options = $options->resolve(array('site' => $this->site, 'filter_choice' => array(
            'request_method' => 'all',
        )));

        $this->assertInstanceOf(
            class_exists('Symfony\Component\Form\ChoiceList\ArrayChoiceList') ? // NEXT_MAJOR: remove condition
                'Symfony\Component\Form\ChoiceList\ArrayChoiceList' :
                'Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList',
            $options['choice_list']
        );
        $this->assertCount(4, $options['choice_list']->getChoices());
    }

    public function testGetRequestMethodChoices()
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->configureOptions($options = new OptionsResolver());

        $options = $options->resolve(array('site' => $this->site));

        // NEXT_MAJOR: remove else clause
        if (class_exists('Symfony\Component\Form\ChoiceList\ArrayChoiceList')) {
            $this->assertInstanceOf(
                'Symfony\Component\Form\ChoiceList\ArrayChoiceList',
                $options['choice_list']
            );
            $views = $options['choice_list']->getChoices();
        } else {
            $this->assertInstanceOf(
                'Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList',
                $options['choice_list']
            );
            $views = $options['choice_list']->getRemainingViews();
        }

        $this->assertCount(3, $views);
        $this->assertRouteNameEquals('all', $views[0]);
        $this->assertRouteNameEquals('get', $views[1]);
        $this->assertRouteNameEquals('get-post', $views[2]);
    }

    public function testPostRequestMethodChoices()
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->configureOptions($options = new OptionsResolver());

        $options = $options->resolve(array('site' => $this->site, 'filter_choice' => array(
            'request_method' => 'post',
        )));

        // NEXT_MAJOR: remove else clause
        if (class_exists('Symfony\Component\Form\ChoiceList\ArrayChoiceList')) {
            $this->assertInstanceOf(
                'Symfony\Component\Form\ChoiceList\ArrayChoiceList',
                $options['choice_list']
            );
            $views = $options['choice_list']->getChoices();
        } else {
            $this->assertInstanceOf(
                'Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList',
                $options['choice_list']
            );
            $views = $options['choice_list']->getRemainingViews();
        }

        $this->assertCount(3, $views);
        $this->assertRouteNameEquals('all', $views[0]);
        $this->assertRouteNameEquals('post', $views[1]);
        $this->assertRouteNameEquals('get-post', $views[2]);
    }

    public function testRootHierarchyChoices()
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->configureOptions($options = new OptionsResolver());

        $options = $options->resolve(array('site' => $this->site, 'filter_choice' => array(
            'hierarchy' => 'root',
            'request_method' => 'all',
        )));

        // NEXT_MAJOR: remove else clause
        if (class_exists('Symfony\Component\Form\ChoiceList\ArrayChoiceList')) {
            $this->assertInstanceOf(
                'Symfony\Component\Form\ChoiceList\ArrayChoiceList',
                $options['choice_list']
            );
            $views = $options['choice_list']->getChoices();
        } else {
            $this->assertInstanceOf(
                'Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList',
                $options['choice_list']
            );
            $views = $options['choice_list']->getRemainingViews();
        }

        $this->assertCount(1, $views);
        $this->assertRouteNameEquals('all', $views[0]);
    }

    public function testChildrenHierarchyChoices()
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->configureOptions($options = new OptionsResolver());

        $options = $options->resolve(array('site' => $this->site, 'filter_choice' => array(
            'hierarchy' => 'children',
            'request_method' => 'all',
        )));

        // NEXT_MAJOR: remove else clause
        if (class_exists('Symfony\Component\Form\ChoiceList\ArrayChoiceList')) {
            $this->assertInstanceOf(
                'Symfony\Component\Form\ChoiceList\ArrayChoiceList',
                $options['choice_list']
            );
            $views = $options['choice_list']->getChoices();
        } else {
            $this->assertInstanceOf(
                'Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList',
                $options['choice_list']
            );
            $views = $options['choice_list']->getRemainingViews();
        }

        $this->assertCount(3, $views);
        $this->assertRouteNameEquals('post', $views[0]);
        $this->assertRouteNameEquals('get', $views[1]);
        $this->assertRouteNameEquals('get-post', $views[2]);
    }

    public function testComplexHierarchyChoices()
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->configureOptions($options = new OptionsResolver());

        $options = $options->resolve(array('site' => $this->site, 'filter_choice' => array(
            'hierarchy' => 'children',
            'request_method' => 'POST',
        )));

        // NEXT_MAJOR: remove else clause
        if (class_exists('Symfony\Component\Form\ChoiceList\ArrayChoiceList')) {
            $this->assertInstanceOf(
                'Symfony\Component\Form\ChoiceList\ArrayChoiceList',
                $options['choice_list']
            );
            $views = $options['choice_list']->getChoices();
        } else {
            $this->assertInstanceOf(
                'Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList',
                $options['choice_list']
            );
            $views = $options['choice_list']->getRemainingViews();
        }

        $this->assertCount(2, $views);
        $this->assertRouteNameEquals('post', $views[0]);
        $this->assertRouteNameEquals('get-post', $views[1]);
    }

    private function assertRouteNameEquals($expected, $choiceView)
    {
        if ($choiceView instanceof LegacyChoiceView) { // NEXT_MAJOR: remove conditional
            return $this->assertSame($expected, $choiceView->label->getRouteName());
        }

        return $this->assertSame($expected, $choiceView->getRouteName());
    }
}
