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

namespace Sonata\PageBundle\Entity;

use Sonata\PageBundle\Model\Block;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseBlock extends Block
{
    public function prePersist(): void
    {
        $this->createdAt = new \DateTime();

        $this->preUpdate();
    }

    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();

        $page = $this->getPage();

        if (null !== $page) {
            $page->setEdited(true);
        }
    }
}
