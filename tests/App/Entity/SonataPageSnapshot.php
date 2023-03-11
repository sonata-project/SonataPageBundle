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

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sonata\PageBundle\Entity\BaseSnapshot;

#[ORM\Entity]
#[ORM\Table(name: 'page__snapshot')]
#[ORM\Index(name: 'idx_snapshot_dates_enabled', columns: ['publication_date_start', 'publication_date_end', 'enabled'])]
class SonataPageSnapshot extends BaseSnapshot
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    protected $id = null;

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
