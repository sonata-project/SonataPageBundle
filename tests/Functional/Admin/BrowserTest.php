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
use Facebook\WebDriver\WebDriverElement;
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSite;
use Sonata\PageBundle\Tests\Functional\BasePantherTestCase;
use Symfony\Component\DomCrawler\Form;

final class BrowserTest extends BasePantherTestCase
{
    public function testComposePage(): void
    {
        $client = self::createPantherClient();
        $crawler = $client->request('GET', '/admin/tests/app/sonatapagepage/1/compose');

        $topContentBlock = $crawler->selectLink('Top content')->link();

        $client->click($topContentBlock);

        $client->waitForElementToContain('.page-composer__container__view__header', 'Top content');

        static::assertCount(0, $crawler->filter('.page-composer__container__child'));

        $crawler->filter('.page-composer__block-type-selector .select2')->first()->click();
        $crawler->filter('.select2-results__options li')->first()->click();
        $crawler->filter('.page-composer__block-type-selector__confirm')->first()->click();

        $client->waitFor('.page-composer__container__child__content form');

        $form = $crawler->selectButton('Create')->form();
        $client->submit($form);

        $client->waitFor('.page-composer__container__child__enabled');

        static::assertCount(1, $crawler->filter('.page-composer__container__child'));
    }

    public function testComposePageBlockErrors(): void
    {
        $client = self::createPantherClient();
        $crawler = $client->request('GET', '/admin/tests/app/sonatapagepage/1/compose');

        $topContentBlock = $crawler->selectLink('Top content')->link();

        $client->click($topContentBlock);

        $client->waitForElementToContain('.page-composer__container__view__header', 'Top content');

        $crawler->filter('.page-composer__block-type-selector .select2')->first()->click();
        $rssFeed = $crawler->filter('.select2-results__options li')->getElement(1);
        \assert($rssFeed instanceof WebDriverElement);

        $rssFeed->click();
        $crawler->filter('.page-composer__block-type-selector__confirm')->first()->click();

        $client->waitFor('.page-composer__container__child__content form');

        $form = $crawler->selectButton('Create')->form();
        $client->submit($form);

        $client->waitFor('.form-group.has-error');

        $uniqid = $this->extractUniqId($form);

        $form[$uniqid.'[settings][url]'] = 'https://docs.sonata-project.org';
        $form[$uniqid.'[settings][title]'] = 'Custom title';

        $client->submit($form);

        $client->waitFor('.page-composer__container__child__enabled');

        static::assertCount(2, $crawler->filter('.page-composer__container__child'));
    }

    protected static function prepareDatabase(): void
    {
        $manager = self::getContainer()->get('doctrine.orm.entity_manager');
        \assert($manager instanceof EntityManagerInterface);

        $site = new SonataPageSite();
        $site->setName('name');
        $site->setHost('localhost');

        $page = new SonataPagePage();
        $page->setName('name');
        $page->setUrl('/');
        $page->setTemplateCode('default');
        $page->setSite($site);

        $manager->persist($site);
        $manager->persist($page);

        $manager->flush();
    }

    private function extractUniqId(Form $form): string
    {
        $url = $form->getUri();

        $queryString = parse_url($url, \PHP_URL_QUERY);
        \assert(\is_string($queryString));

        parse_str($queryString, $queryParameters);
        \assert(\is_string($queryParameters['uniqid']));

        return $queryParameters['uniqid'] ?? '';
    }
}
