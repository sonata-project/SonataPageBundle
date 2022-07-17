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

namespace Sonata\PageBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\TransformerInterface;
use Sonata\PageBundle\Service\CreateSnapshotService;

final class CreateSnapshotServiceTest extends TestCase
{
    public function testCreateBySite(): void
    {
        //Mocks
        $snapshotManagerMock = $this->createMock(SnapshotManagerInterface::class);

        $pageManagerMock = $this->createMock(PageManagerInterface::class);
        $pageManagerMock
            ->method('findBy')
            ->willReturn([$this->createMock(PageInterface::class)]);

        $transformerMock = $this->createMock(TransformerInterface::class);
        $transformerMock
            ->method('create')
            ->willReturn($this->createMock(SnapshotInterface::class));

        $siteMock = $this->createMock(SiteInterface::class);

        //Asserts mocks
        $transformerMock
            ->expects(static::once())
            ->method('create');

        //Execute code
        $createSnapshotService = new CreateSnapshotService(
            $snapshotManagerMock,
            $pageManagerMock,
            $transformerMock
        );
        $createSnapshotService->createBySite($siteMock);
    }
}
