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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\OptimisticLockException;
use Sonata\PageBundle\Entity\SnapshotManager;
use Sonata\PageBundle\Tests\App\AppKernel;
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSnapshot;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SnapshotManagerTest extends KernelTestCase
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var SnapshotManager
     */
    protected $snapshotManager;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->snapshotManager = $kernel->getContainer()
            ->get('sonata.page.manager.snapshot');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testFunctionalEnableSnapshots()
    {
        // disable the auto increment id
        $metadata = $this->entityManager->getClassMetadata(SonataPagePage::class);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());

        // Try to write Doctrine Fixture?
        $page = new SonataPagePage();
        $page->setId(456);
        $page->setName('Name 456');
        $page->setEnabled(true);
        $page->setTemplateCode('TemplateCode');
        $page->setRouteName('/page456');

        $this->entityManager->persist($page);
        $this->entityManager->flush();

        // disable the auto increment id
        $metadata = $this->entityManager->getClassMetadata(SonataPageSnapshot::class);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());

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

        static::assertDateTimeEquals($date, $snapshot->getPublicationDateStart());
        static::assertNull($snapshot->getPublicationDateEnd());

        static::assertDateTimeEquals($date, $snapshot2->getPublicationDateEnd());
    }

    public static function assertDateTimeEquals(\DateTime $expected, \DateTime $actual)
    {
        static::assertSame($expected->format('c'), $actual->format('c'));
    }

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }
}
