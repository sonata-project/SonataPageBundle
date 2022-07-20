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

use Sonata\Doctrine\Entity\BaseEntityManager;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;

/**
 * This class manages SiteInterface persistency with the Doctrine ORM.
 *
 * @extends BaseEntityManager<SiteInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SiteManager extends BaseEntityManager implements SiteManagerInterface
{
}
