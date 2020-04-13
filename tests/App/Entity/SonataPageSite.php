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

namespace Sonata\PageBundle\Tests\App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\PageBundle\Entity\BaseSite;

/**
 * @ORM\Entity
 * @ORM\Table(name="page__site")
 * @ORM\HasLifecycleCallbacks
 */
class SonataPageSite extends BaseSite
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        parent::prePersist();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        parent::preUpdate();
    }
}
