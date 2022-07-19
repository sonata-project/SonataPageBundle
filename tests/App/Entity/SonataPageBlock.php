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
use Sonata\PageBundle\Entity\BaseBlock;

/**
 * @ORM\Entity
 * @ORM\Table(name="page__block")
 * @ORM\HasLifecycleCallbacks
 */
class SonataPageBlock extends BaseBlock
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Sonata\PageBundle\Tests\App\Entity\SonataPageBlock",
     *     mappedBy="parent", cascade={"remove", "persist"}, orphanRemoval=true
     * )
     * @ORM\OrderBy({"position"="ASC"})
     *
     * @var SonataPageBlock[]
     */
    protected $children;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Sonata\PageBundle\Tests\App\Entity\SonataPageBlock",
     *     inversedBy="children"
     * )
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var SonataPageBlock
     */
    protected $parent;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Sonata\PageBundle\Tests\App\Entity\SonataPagePage",
     *     inversedBy="blocks", cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var SonataPagePage
     */
    protected $page;

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
