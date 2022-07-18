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
use Sonata\PageBundle\Entity\BaseSnapshot;

/**
 * @ORM\Entity
 * @ORM\Table(name="page__snapshot", indexes={
 *     @ORM\Index(
 *         name="idx_snapshot_dates_enabled", columns={"publication_date_start", "publication_date_end","enabled"
 *     })
 * })
 */
class SonataPageSnapshot extends BaseSnapshot
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
     *     cascade={"persist"}
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

    public function setId($id)
    {
        $this->id = $id;
    }
}
