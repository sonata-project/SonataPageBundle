<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Entity;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\PageBundle\Model\SiteManagerInterface;

/**
 * This class manages SiteInterface persistency with the Doctrine ORM
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SiteManager extends BaseEntityManager implements SiteManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function save($site, $andFlush = true)
    {
        parent::save($site, $andFlush);

        return $site;
    }
}
