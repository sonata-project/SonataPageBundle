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

namespace Sonata\PageBundle\Tests\Form\Type;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Form\Type\PageSelectorType;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Tests\Model\Page;
use Sonata\PageBundle\Tests\Model\Site;
use Symfony\Component\Form\Extension\Core\View\ChoiceView as LegacyChoiceView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageSelectorTypeTest extends TestCase
{
    protected $pages;

    protected $site;

    protected function setUp(): void
    {
        $pages = [];

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
        $manager = $this->createMock(PageManagerInterface::class);

        $manager->expects($this->any())
            ->method('loadPages')
            ->willReturn($this->pages);

        return $manager;
    }

    /**
     * @group legacy
     */
    public function testNoSite(): void
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->configureOptions($options = new OptionsResolver());

        $options = $options->resolve(['filter_choice' => [
            'request_method' => 'all',
        ]]);

        $this->assertSame([], $options['choices']);
    }

    /**
     * @group legacy
     */
    public function testAllRequestMethodChoices(): void
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->configureOptions($options = new OptionsResolver());

        $options = $options->resolve(['site' => $this->site, 'filter_choice' => [
            'request_method' => 'all',
        ]]);

        $this->assertCount(4, $options['choices']);
    }

    public function testGetRequestMethodChoices(): void
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->configureOptions($options = new OptionsResolver());

        $options = $options->resolve(['site' => $this->site]);

        $views = $options['choices'];

        $this->assertCount(3, $views);
        $this->assertRouteNameEquals('all', $views[1]);
        $this->assertRouteNameEquals('get', $views[3]);
        $this->assertRouteNameEquals('get-post', $views[4]);
    }

    public function testPostRequestMethodChoices(): void
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->configureOptions($options = new OptionsResolver());

        $options = $options->resolve(['site' => $this->site, 'filter_choice' => [
            'request_method' => 'post',
        ]]);

        $views = $options['choices'];

        $this->assertCount(3, $views);
        $this->assertRouteNameEquals('all', $views[1]);
        $this->assertRouteNameEquals('post', $views[2]);
        $this->assertRouteNameEquals('get-post', $views[4]);
    }

    public function testRootHierarchyChoices(): void
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->configureOptions($options = new OptionsResolver());

        $options = $options->resolve(['site' => $this->site, 'filter_choice' => [
            'hierarchy' => 'root',
            'request_method' => 'all',
        ]]);

        $views = $options['choices'];

        $this->assertCount(1, $views);
        $this->assertRouteNameEquals('all', $views[1]);
    }

    public function testChildrenHierarchyChoices(): void
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->configureOptions($options = new OptionsResolver());

        $options = $options->resolve(['site' => $this->site, 'filter_choice' => [
            'hierarchy' => 'children',
            'request_method' => 'all',
        ]]);

        $views = $options['choices'];

        $this->assertCount(3, $views);
        $this->assertRouteNameEquals('post', $views[2]);
        $this->assertRouteNameEquals('get', $views[3]);
        $this->assertRouteNameEquals('get-post', $views[4]);
    }

    public function testComplexHierarchyChoices(): void
    {
        $pageSelector = new PageSelectorType($this->getPageManager());

        $pageSelector->configureOptions($options = new OptionsResolver());

        $options = $options->resolve(['site' => $this->site, 'filter_choice' => [
            'hierarchy' => 'children',
            'request_method' => 'POST',
        ]]);

        $views = $options['choices'];

        $this->assertCount(2, $views);
        $this->assertRouteNameEquals('post', $views[2]);
        $this->assertRouteNameEquals('get-post', $views[4]);
    }

    private function assertRouteNameEquals($expected, $choiceView)
    {
        if ($choiceView instanceof LegacyChoiceView) { // NEXT_MAJOR: remove conditional
            return $this->assertSame($expected, $choiceView->label->getRouteName());
        }

        return $this->assertSame($expected, $choiceView->getRouteName());
    }
}
