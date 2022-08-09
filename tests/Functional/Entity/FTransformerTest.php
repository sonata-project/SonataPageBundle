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
final class FTransformerTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    private TransformerInterface $transformer;

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
        $this->entityManager->getRepository(SonataPagePage::class)
            ->createQueryBuilder('p')->delete()->getQuery()->execute();

        parent::tearDown();

        $this->entityManager->close();
    }

    /**
     * @param class-string $class
     */
    public function disableAutoIncrement(string $class): void
    {
        $metadata = $this->entityManager->getClassMetadata($class);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());
    }

    /**
     * @return array{\DateTime, int, array<string, mixed>}[]
     */
    public function createProvider(): array
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

        return [
            [$datetime, 0, []],
            [$datetime, 0, $settings],
        ];
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
        $parentPage->setSite($site);
        $parentPage->setCreatedAt($datetime);
        $parentPage->setUpdatedAt($datetime);

        $page = new SonataPagePage();
        $page->setId(123);
        $page->setName('Page Child');
        $page->setTitle('Page Child Title');
        $page->setUrl('/get-child');
        $page->setSite($site);
        $page->setCreatedAt($datetime);
        $page->setUpdatedAt($datetime);
        $page->addBlock($block1);
        $page->addBlock($block2);
        $parentPage->addChild($page);

        $snapshot = $this->transformer->create($page);

        static::assertSame($page->getUrl(), $snapshot->getUrl());
        static::assertSame($page->getName(), $snapshot->getName());
        static::assertSameArray($this->getTestContent($datetime, $position, $settings), $snapshot->getContent());
    }

    /**
     * @dataProvider createProvider
     *
     * @param array<string, ?mixed> $settings
     */
    public function testLoadSnapshotToPage(\DateTimeInterface $datetime, ?int $position, array $settings): void
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
     * @dataProvider createProvider
     *
     * @param array<string, ?mixed> $settings
     */
    public function testLoadBlock(\DateTimeInterface $datetime, ?int $position, array $settings): void
    {
        $page = new SonataPagePage();

        $block = $this->transformer->loadBlock($this->getTestBlockArray($datetime, $position, $settings), $page);

        static::assertSame(123, $block->getId());
    }

    /**
     * @param array<string, ?mixed> $settings
     *
     * @return PageContent
     */
    protected function getTestContent(\DateTimeInterface $datetime, ?int $position, array $settings): array
    {
        // for some reason, the order is different on the functional tests
        return [
            'id' => 123,
            'parent_id' => 789,
//            'javascript' => null,
//            'stylesheet' => null,
//            'raw_headers' => null,
            'title' => 'Page Child Title',
//            'meta_description' => null,
//            'meta_keyword' => null,
            'name' => 'Page Child',
//            'slug' => null,
//            'template_code' => null,
            'request_method' => 'GET|POST|HEAD|DELETE|PUT',
            'created_at' => $datetime->format('U'),
            'updated_at' => $datetime->format('U'),
            'blocks' => [
                $this->getTestBlockArray($datetime, $position, $settings),
            ],
        ];
    }

    /**
     * @param array<string, ?mixed> $settings
     *
     * @return BlockContent
     */
    protected function getTestBlockArray(\DateTimeInterface $datetime, ?int $position, array $settings): array
    {
        // for some reason, the order is different on the functional tests
        return [
            'id' => 123, // probably doctrine says the type must be int instead of also string
            'name' => 'block1',
            'enabled' => false,
            'position' => $position,
            'settings' => $settings,
            'type' => 'type',
            'created_at' => $datetime->format('U'),
            'updated_at' => $datetime->format('U'),
            'blocks' => [
                [
                    'id' => 234,
                    'name' => 'block2',
                    'enabled' => false,
                    'position' => $position,
                    'settings' => $settings,
                    'type' => 'type',
                    'created_at' => $datetime->format('U'),
                    'updated_at' => $datetime->format('U'),
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
