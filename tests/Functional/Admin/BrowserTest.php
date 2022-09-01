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

namespace Sonata\PageBundle\Tests\Functional\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Sonata\PageBundle\Tests\Functional\BasePantherTestCase;

final class BrowserTest extends BasePantherTestCase
{
    public function testComposePage(): void
    {
        $client = self::createPantherClient();
        $crawler = $client->request('GET', '/admin/tests/app/sonatapagepage/1/compose');

        $topContentBlock = $crawler->selectLink('Top content')->link();

        $client->click($topContentBlock);

        $client->waitForElementToContain('.page-composer__container__view__header', 'Top content');

        static::assertCount(0, $crawler->filter('.page-composer__container__children li'));

        $crawler->filter('.page-composer__block-type-selector .select2')->first()->click();
        $crawler->filter('.select2-results__options li')->first()->click();
        $crawler->filter('.page-composer__block-type-selector__confirm')->first()->click();

        $client->waitFor('.page-composer__container__child');

        $crawler->filter('.page-composer__container__child button')->first()->click();

        $client->waitFor('.page-composer__container__child__enabled');

        static::assertCount(1, $crawler->filter('.page-composer__container__children li'));
    }

    /**
     * @psalm-suppress UndefinedPropertyFetch
     */
    protected static function prepareDatabase(): void
    {
        // TODO: Simplify this when dropping support for Symfony 4.
        // @phpstan-ignore-next-line
        $container = method_exists(self::class, 'getContainer') ? self::getContainer() : self::$container;
        $manager = $container->get('doctrine.orm.entity_manager');
        \assert($manager instanceof EntityManagerInterface);

        $page = new SonataPagePage();
        $page->setName('name');
        $page->setUrl('/');
        $page->setTemplateCode('default');

        $manager->persist($page);

        $manager->flush();
    }
}
