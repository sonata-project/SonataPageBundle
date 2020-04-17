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
use Sonata\PageBundle\Entity\BasePage;

/**
 * @ORM\Entity
 * @ORM\Table(name="page__page")
 * @ORM\HasLifecycleCallbacks
 */
class SonataPagePage extends BasePage
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
     *     targetEntity="Sonata\PageBundle\Tests\App\Entity\SonataPagePage",
     *     mappedBy="parent", cascade={"persist"}, orphanRemoval=false
     * )
     * @ORM\OrderBy({"position"="ASC"})
     *
     * @var SonataPagePage[]
     */
    protected $children;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Sonata\PageBundle\Tests\App\Entity\SonataPageBlock",
     *     mappedBy="page", cascade={"remove", "persist", "refresh", "merge", "detach"}, orphanRemoval=false
     * )
     * @ORM\OrderBy({"position"="ASC"})
     *
     * @var SonataPageBlock[]
     */
    protected $blocks;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Sonata\PageBundle\Tests\App\Entity\SonataPageSite",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var SonataPageSite
     */
    protected $site;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Sonata\PageBundle\Tests\App\Entity\SonataPagePage",
     *     inversedBy="children", cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var SonataPagePage
     */
    protected $parent;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Sonata\PageBundle\Tests\App\Entity\SonataPagePage",
     *     mappedBy="target", orphanRemoval=false
     * )
     *
     * @var SonataPagePage[]
     */
    protected $sources;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Sonata\PageBundle\Tests\App\Entity\SonataPagePage",
     *     inversedBy="sources", cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="target_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var SonataPagePage
     */
    protected $target;

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
