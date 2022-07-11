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

namespace Sonata\PageBundle\Tests;

use Cocur\Slugify\Slugify;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Model\Page as BasePage;
use Sonata\PageBundle\SonataPageBundle;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Page extends BasePage
{
    /**
     * Returns the id.
     */
    public function getId()
    {
        // TODO: Implement getId() method.
    }
}

final class SonataPageBundleTest extends TestCase
{
    /**
     * @dataProvider getSlug
     */
    public function testBoot($text, $expected): void
    {
        $bundle = new SonataPageBundle();
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(static::exactly(2))->method('getParameter')->willReturnCallback(static function ($value) {
            if ('sonata.page.page.class' === $value) {
                return Page::class;
            }

            if ('sonata.page.slugify_service' === $value) {
                return 'slug_service';
            }
        });
        $container->expects(static::once())->method('get')->willReturn(Slugify::create());

        $bundle->setContainer($container);
        $bundle->boot();

        $page = new Page();
        $page->setSlug($text);
        static::assertSame($page->getSlug(), $expected);
    }

    public function getSlug(): array
    {
        return [
            ['Salut comment ca va ?',  'salut-comment-ca-va'],
            ['òüì',  'ouei'],
        ];
    }
}
