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

namespace Sonata\PageBundle\Tests\Functional\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sonata\PageBundle\Model\TransformerInterface;
use Sonata\PageBundle\Tests\App\AppKernel;
use Sonata\PageBundle\Tests\App\Entity\SonataPageBlock;
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSite;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSnapshot;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @phpstan-import-type PageContent from TransformerInterface
 * @phpstan-import-type BlockContent from TransformerInterface
 */
final class TransformerTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    private TransformerInterface $transformer;

    /**
     * @var array<string, AbstractIdGenerator>
     */
    private array $storedIdGenerators = [];

    /**
     * @var array<string, 1|2|3|4|5|6|7>
     */
    private array $storedIdGeneratorTypes = [];

    /**
     * @var array<string, array<string, list<string>>>
     */
    private array $storedLifeCycles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();

        $entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        \assert($entityManager instanceof EntityManagerInterface);

        $transformer = $kernel->getContainer()->get('sonata.page.transformer');
        \assert($transformer instanceof TransformerInterface);

        $this->entityManager = $entityManager;
        $this->transformer = $transformer;
    }

    protected function tearDown(): void
    {
        // clear test entities
        $this->entityManager->getRepository(SonataPageSnapshot::class)
            ->createQueryBuilder('s')->delete()->getQuery()->execute();
        $this->entityManager->getRepository(SonataPageBlock::class)
            ->createQueryBuilder('b')->delete()->getQuery()->execute();
        $this->entityManager->getRepository(SonataPagePage::class)
            ->createQueryBuilder('p')->delete()->getQuery()->execute();

        parent::tearDown();

        // need to restore the lifecycles, these aren't reset by mapping
        foreach ($this->storedIdGenerators as $key => $generator) {
            $this->entityManager->getClassMetadata($key)->setIdGenerator($generator);
        }
        foreach ($this->storedIdGeneratorTypes as $key => $generatorType) {
            $this->entityManager->getClassMetadata($key)->setIdGeneratorType($generatorType);
        }
        foreach ($this->storedLifeCycles as $key => $cycle) {
            $this->entityManager->getClassMetadata($key)->setLifecycleCallbacks($cycle);
        }

        $this->entityManager->close();
    }

    /**
     * @param class-string $class
     */
    public function disableAutoIncrement(string $class): void
    {
        $metadata = $this->entityManager->getClassMetadata($class);
        $this->storedIdGeneratorTypes[$class] = $metadata->generatorType;
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);
        $this->storedIdGenerators[$class] = $metadata->idGenerator;
        $metadata->setIdGenerator(new AssignedGenerator());
        // disable lifecycle to not update created and updated
        $this->storedLifeCycles[$class] = $metadata->lifecycleCallbacks;
        $metadata->setLifecycleCallbacks([]);
    }

    /**
     * @return iterable<array{\DateTime, ?int, array<string, mixed>}>
     */
    public function createProvider(): iterable
    {
        $datetime = new \DateTime();

        $settings = [
            'url' => 'https://facebook.com',
            'title' => 'RSS feed',
            'translation_domain' => null,
            'icon' => 'fa fa-rss-square',
            'class' => null,
            'template' => '@SonataBlock/Block/block_core_rss.html.twig',
            'safe_labels' => false,
            'include_homepage_link' => true,
            'context' => false,
        ];
        yield [$datetime, 0, []];
        yield [$datetime, 0, $settings];
        yield [$datetime, null, []];
        yield [$datetime, null, $settings];
    }

    /**
     * @return iterable<array{\DateTime, int|string|null, array<string, mixed>}>
     */
    public function loadProvider(): iterable
    {
        $datetime = new \DateTime();

        $settings = [
            'url' => 'https://facebook.com',
            'title' => 'RSS feed',
            'translation_domain' => null,
            'icon' => 'fa fa-rss-square',
            'class' => null,
            'template' => '@SonataBlock/Block/block_core_rss.html.twig',
            'safe_labels' => false,
            'include_homepage_link' => true,
            'context' => false,
        ];
        yield [$datetime, 0, []];
        yield [$datetime, 0, $settings];
        yield [$datetime, null, []];
        yield [$datetime, null, $settings];
        yield [$datetime, '0', []];
        yield [$datetime, '0', $settings];
        yield [$datetime, '', []];
        yield [$datetime, '', $settings];
    }

    /**
     * @dataProvider createProvider
     *
     * @param array<string, ?mixed> $settings
     */
    public function testCreateSnapshot(\DateTime $datetime, ?int $position, array $settings): void
    {
        $site = new SonataPageSite();

        $block1 = new SonataPageBlock();
        $block1->setId(123);
        $block1->setName('block1');
        $block1->setType('type');
        if (null !== $position) {
            $block1->setPosition($position);
        }
        $block1->setSettings($settings);
        $block1->setCreatedAt($datetime);
        $block1->setUpdatedAt($datetime);

        $block2 = new SonataPageBlock();
        $block2->setId(234);
        $block2->setName('block2');
        $block2->setType('type');
        if (null !== $position) {
            $block2->setPosition($position);
        }
        $block2->setSettings($settings);
        $block2->setCreatedAt($datetime);
        $block2->setUpdatedAt($datetime);

        $block1->addChild($block2);

        $parentPage = new SonataPagePage();
        $parentPage->setId(789);
        $parentPage->setName('Page Parent');
        $parentPage->setUrl('/get-parent');
        $parentPage->setTemplateCode('template');
        $parentPage->setSite($site);
        $parentPage->setCreatedAt($datetime);
        $parentPage->setUpdatedAt($datetime);

        $page = new SonataPagePage();
        $page->setId(123);
        $page->setName('Page Child');
        $page->setTitle('Page Child Title');
        $page->setUrl('/get-child');
        $page->setTemplateCode('template');
        $page->setSite($site);
        $page->setCreatedAt($datetime);
        $page->setUpdatedAt($datetime);
        $page->addBlock($block1);
        $page->addBlock($block2);
        $parentPage->addChild($page);

        $snapshot = $this->transformer->create($page);

        static::assertSame($page->getUrl(), $snapshot->getUrl());
        static::assertSame($page->getName(), $snapshot->getName());
        // check the blocks array only containing integer
        $snapshotContent = $snapshot->getContent();
        static::assertNotNull($snapshotContent);
        static::assertArrayHasKey('blocks', $snapshotContent);
        static::assertContainsOnly('int', array_keys($snapshotContent['blocks']));

        $testContent = $this->getTestContent($datetime, $position, $settings);

        // if position is null for some reason, it isn't serialized because of null,
        // so I need to remove it from expected data again
        if (null === $position) {
            unset($testContent['blocks'][0]['position'], $testContent['blocks'][0]['blocks'][0]['position']);
        }

        static::assertSameArray($testContent, $snapshotContent);
    }

    /**
     * @dataProvider createProvider
     *
     * @param array<string, ?mixed> $settings
     */
    public function testCreateSnapshotOverDoctrine(\DateTime $datetime, ?int $position, array $settings): void
    {
        $this->disableAutoIncrement(SonataPageSite::class);
        $this->disableAutoIncrement(SonataPagePage::class);
        $this->disableAutoIncrement(SonataPageBlock::class);

        $site = new SonataPageSite();
        $site->setId(12);
        $site->setName('Site Name');
        $site->setHost('localhost');
        $site->setCreatedAt($datetime);
        $site->setUpdatedAt($datetime);
        $this->entityManager->persist($site);
        $this->entityManager->flush();

        $block1 = new SonataPageBlock();
        $block1->setId(123);
        $block1->setName('block1');
        $block1->setType('type');
        if (null !== $position) {
            $block1->setPosition($position);
        }
        $block1->setSettings($settings);
        $block1->setCreatedAt($datetime);
        $block1->setUpdatedAt($datetime);

        $block2 = new SonataPageBlock();
        $block2->setId(234);
        $block2->setName('block2');
        $block2->setType('type');
        if (null !== $position) {
            $block2->setPosition($position);
        }
        $block2->setSettings($settings);
        $block2->setCreatedAt($datetime);
        $block2->setUpdatedAt($datetime);

        $this->entityManager->persist($block1);
        $block1->addChild($block2);
        $this->entityManager->persist($block2);
        $this->entityManager->flush();

        $parentPage = new SonataPagePage();
        $parentPage->setId(789);
        $parentPage->setName('Page Parent');
        $parentPage->setUrl('/get-parent');
        $parentPage->setSite($site);
        $parentPage->setTemplateCode('template');
        $parentPage->setCreatedAt($datetime);
        $parentPage->setUpdatedAt($datetime);
        $this->entityManager->persist($parentPage);

        $page = new SonataPagePage();
        $page->setId(123);
        $page->setName('Page Child');
        $page->setTitle('Page Child Title');
        $page->setUrl('/get-child');
        $page->setSite($site);
        $page->setTemplateCode('template');
        $page->setCreatedAt($datetime);
        $page->setUpdatedAt($datetime);
        $page->addBlock($block1);
        $page->addBlock($block2);
        $parentPage->addChild($page);
        $this->entityManager->persist($page);
        $this->entityManager->flush();

        $this->entityManager->clear();
        /**
         * @var SonataPagePage $page
         */
        $page = $this->entityManager->find(sonataPagePage::class, 123);

        $snapshot = $this->transformer->create($page);

        static::assertSame($page->getUrl(), $snapshot->getUrl());
        static::assertSame($page->getName(), $snapshot->getName());
        // check the blocks array only containing integer
        $snapshotContent = $snapshot->getContent();
        static::assertNotNull($snapshotContent);
        static::assertArrayHasKey('blocks', $snapshotContent);
        static::assertContainsOnly('int', array_keys($snapshotContent['blocks']));

        $testContent = $this->getTestContent($datetime, $position, $settings);

        // if position is null for some reason, it isn't serialized because of null,
        // so I need to remove it from expected data again
        if (null === $position) {
            unset($testContent['blocks'][0]['position'], $testContent['blocks'][0]['blocks'][0]['position']);
        }

        static::assertSameArray($testContent, $snapshotContent);
    }

    /**
     * @dataProvider loadProvider
     *
     * @param array<string, ?mixed> $settings
     */
    public function testLoadSnapshotToPage(\DateTimeInterface $datetime, int|string|null $position, array $settings): void
    {
        $snapshot = new SonataPageSnapshot();
        $snapshot->setContent($this->getTestContent($datetime, $position, $settings));
        $snapshot->setUrl('/get-child');
        $page = $this->transformer->load($snapshot);

        static::assertSame(123, $page->getId());
        static::assertSame('Page Child', $page->getName());
        static::assertSame('Page Child Title', $page->getTitle());
        static::assertSame('/get-child', $page->getUrl());
    }

    /**
     * @dataProvider loadProvider
     *
     * @param array<string, ?mixed> $settings
     */
    public function testLoadBlock(\DateTimeInterface $datetime, int|string|null $position, array $settings): void
    {
        $page = new SonataPagePage();

        $block = $this->transformer->loadBlock($this->getTestBlockArray($datetime, $position, $settings), $page);

        static::assertSame(123, $block->getId());
        static::assertCount(1, $block->getChildren());
    }

    /**
     * @param array<string, ?mixed> $settings
     *
     * @return PageContent
     */
    protected function getTestContent(\DateTimeInterface $datetime, int|string|null $position, array $settings): array
    {
        /**
         * @var numeric-string $dateTimeString
         */
        $dateTimeString = $datetime->format('U');

        // for some reason, the order is different on the functional tests
        return [
            'id' => 123,
            'parent_id' => 789,
            'title' => 'Page Child Title',
            'name' => 'Page Child',
            'template_code' => 'template',
            'request_method' => 'GET|POST|HEAD|DELETE|PUT',
            'created_at' => $dateTimeString,
            'updated_at' => $dateTimeString,
            'blocks' => [
                $this->getTestBlockArray($datetime, $position, $settings),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $settings
     *
     * @return BlockContent
     */
    protected function getTestBlockArray(\DateTimeInterface $datetime, int|string|null $position, array $settings): array
    {
        /**
         * @var numeric-string $dateTimeString
         */
        $dateTimeString = $datetime->format('U');

        // for some reason, the order is different on the functional tests
        return [
            'id' => 123, // probably doctrine says the type must be int instead of also string
            'name' => 'block1',
            'enabled' => false,
            'position' => $position,
            'settings' => $settings,
            'type' => 'type',
            'created_at' => $dateTimeString,
            'updated_at' => $dateTimeString,
            'blocks' => [
                [
                    'id' => 234,
                    'name' => 'block2',
                    'enabled' => false,
                    'position' => $position,
                    'settings' => $settings,
                    'type' => 'type',
                    'created_at' => $dateTimeString,
                    'updated_at' => $dateTimeString,
                    'blocks' => [],
                ],
            ],
        ];
    }

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    /**
     * @phpstan-param PageContent|null $expected
     * @phpstan-param PageContent|null $actual
     */
    protected static function assertSameArray(?array $expected, ?array $actual): void
    {
        if (\is_array($expected)) {
            static::recur_ksort($expected);
        }
        if (\is_array($actual)) {
            static::recur_ksort($actual);
        }
        static::assertSame($expected, $actual);
    }

    /**
     * @param array<string, mixed|array<string, mixed>> $array
     */
    protected static function recur_ksort(array &$array): bool
    {
        /**
         * @var mixed|array<string, mixed> $value
         */
        foreach ($array as &$value) {
            if (\is_array($value)) {
                /*
                 * @var array<string, mixed> $value
                 */
                static::recur_ksort($value);
            }
        }

        return ksort($array);
    }
}
