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

namespace Sonata\PageBundle\Tests\Functional\Snapshot;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\OptimisticLockException;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSnapshot;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SnapshotManagerTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    private SnapshotManagerInterface $snapshotManager;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();

        $entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        \assert($entityManager instanceof EntityManagerInterface);
        $snapshotManager = $kernel->getContainer()->get('sonata.page.manager.snapshot');
        \assert($snapshotManager instanceof SnapshotManagerInterface);

        $this->entityManager = $entityManager;
        $this->snapshotManager = $snapshotManager;
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
     * @throws ORMException
     * @throws OptimisticLockException|ORMException
     */
    public function testFunctionalEnableSnapshots(): void
    {
        // disable the auto increment id
        $this->disableAutoIncrement(SonataPagePage::class);

        // Try to write Doctrine Fixture?
        $page = new SonataPagePage();
        $page->setId(456);
        $page->setName('Name 456');
        $page->setEnabled(true);
        $page->setTemplateCode('TemplateCode');
        $page->setRouteName('/page456');

        $this->entityManager->persist($page);
        $this->entityManager->flush();

        $this->disableAutoIncrement(SonataPageSnapshot::class);

        $snapshot = new SonataPageSnapshot();
        $snapshot->setId(123);
        $snapshot->setPage($page);
        $snapshot->setEnabled(true);
        $snapshot->setName('Name 123');
        $snapshot->setRouteName('/snapshot123');
        $this->entityManager->persist($snapshot);
        $this->entityManager->flush();

        $snapshot2 = new SonataPageSnapshot();
        $snapshot2->setId(789);
        $snapshot2->setPage($page);
        $snapshot2->setEnabled(true);
        $snapshot2->setName('Name 789');
        $snapshot2->setRouteName('/snapshot789');
        $this->entityManager->persist($snapshot2);
        $this->entityManager->flush();

        $date = new \DateTime();

        $this->snapshotManager->enableSnapshots([$snapshot], $date);

        static::assertNull($snapshot2->getPublicationDateEnd());
        $this->entityManager->refresh($snapshot2);

        $snapshotPublicationDateStart = $snapshot->getPublicationDateStart();

        static::assertNotNull($snapshotPublicationDateStart);
        static::assertDateTimeEquals($date, $snapshotPublicationDateStart);
        static::assertNull($snapshot->getPublicationDateEnd());

        $snapshotPublicationDateEnd = $snapshot2->getPublicationDateEnd();

        // @phpstan-ignore-next-line https://github.com/phpstan/phpstan/discussions/8586
        static::assertNotNull($snapshotPublicationDateEnd);
        static::assertDateTimeEquals($date, $snapshotPublicationDateEnd);
    }

    public function testCleanupPages(): void
    {
        $this->disableAutoIncrement(SonataPagePage::class);

        // Try to write Doctrine Fixture?
        $page = new SonataPagePage();
        $page->setId(456);
        $page->setName('Name 456');
        $page->setEnabled(true);
        $page->setTemplateCode('TemplateCode');
        $page->setRouteName('/page456');

        $this->entityManager->persist($page);
        $this->entityManager->flush();

        $date = new \DateTime();

        $this->disableAutoIncrement(SonataPageSnapshot::class);

        $snapshot = new SonataPageSnapshot();
        $snapshot->setId(123);
        $snapshot->setPage($page);
        $snapshot->setEnabled(true);
        $snapshot->setName('Name 123');
        $snapshot->setRouteName('/snapshot123');
        $this->entityManager->persist($snapshot);
        $this->entityManager->flush();

        $snapshot2 = new SonataPageSnapshot();
        $snapshot2->setId(789);
        $snapshot2->setPage($page);
        $snapshot2->setEnabled(true);
        $snapshot2->setName('Name 789');
        $snapshot2->setRouteName('/snapshot789');
        $snapshot2->setPublicationDateEnd($date);
        $this->entityManager->persist($snapshot2);
        $this->entityManager->flush();

        $this->snapshotManager->cleanup($page, 1);

        // asking if entity manager contains entity isn't enough, see below
        $repo = $this->entityManager->getRepository(SonataPageSnapshot::class);
        $all = $repo->findAll();
        static::assertCount(1, $all);
        static::assertContains($snapshot, $all);
        static::assertNotContains($snapshot2, $all);

        // object still exist in entityManager, that is by design
        static::assertTrue($this->entityManager->contains($snapshot));
        static::assertTrue($this->entityManager->contains($snapshot2));

        // EntityManager clear is brutal, can't use the entities anymore
        $this->entityManager->clear();

        // deleted entity shouldn't exist anymore
        static::assertNotNull($repo->find(123));
        static::assertNull($repo->find(789));
    }

    public function testCleanupAllPages(): void
    {
        $this->disableAutoIncrement(SonataPagePage::class);

        // Try to write Doctrine Fixture?
        $page = new SonataPagePage();
        $page->setId(234);
        $page->setName('Name 234');
        $page->setEnabled(true);
        $page->setTemplateCode('TemplateCode');
        $page->setRouteName('/page234');

        $this->entityManager->persist($page);
        $this->entityManager->flush();

        $page2 = new SonataPagePage();
        $page2->setId(456);
        $page2->setName('Name 456');
        $page2->setEnabled(true);
        $page2->setTemplateCode('TemplateCode');
        $page2->setRouteName('/page456');

        $this->entityManager->persist($page2);
        $this->entityManager->flush();

        $date = new \DateTime();

        $this->disableAutoIncrement(SonataPageSnapshot::class);

        $snapshot = new SonataPageSnapshot();
        $snapshot->setId(123);
        $snapshot->setPage($page2);
        $snapshot->setEnabled(true);
        $snapshot->setName('Name 123');
        $snapshot->setRouteName('/snapshot123');
        $snapshot->setPublicationDateEnd($date);
        $this->entityManager->persist($snapshot);
        $this->entityManager->flush();

        // this should have no snapshot to keep, because the page doesn't have any
        // but also no snapshot should be deleted
        $this->snapshotManager->cleanup($page, 1);

        // asking if entity manager contains entity isn't enough, see below
        $repo = $this->entityManager->getRepository(SonataPageSnapshot::class);
        $all = $repo->findAll();

        // The Snapshot should still be there
        static::assertCount(1, $all);
        static::assertContains($snapshot, $all);

        // object still exist in entityManager, that is by design
        static::assertTrue($this->entityManager->contains($snapshot));

        // EntityManager clear is brutal, can't use the entities anymore
        $this->entityManager->clear();

        static::assertNotNull($repo->find(123));
    }

    public static function assertDateTimeEquals(\DateTimeInterface $expected, \DateTimeInterface $actual): void
    {
        static::assertSame($expected->format('c'), $actual->format('c'));
    }
}
