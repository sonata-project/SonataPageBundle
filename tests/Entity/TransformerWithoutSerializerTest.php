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

namespace Sonata\PageBundle\Tests\Entity;

use Doctrine\Persistence\ManagerRegistry;
use Sonata\PageBundle\Entity\Transformer;
use Sonata\PageBundle\Model\TransformerInterface;

/**
 * @group legacy
 */
final class TransformerWithoutSerializerTest extends TransformerTest
{
    protected function setUpTransformer(): TransformerInterface
    {
        $registry = $this->createMock(ManagerRegistry::class);

        return new Transformer(
            $this->snapshotManager,
            $this->pageManager,
            $this->blockManager,
            $registry
        );
    }
}
