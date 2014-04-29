<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Model;

use Sonata\CoreBundle\Model\ManagerInterface;

/**
 * SiteManagerInterface
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface SiteManagerInterface extends ManagerInterface
{
    /**
     * Retrieve sites, based on given criteria, a page at a time.
     *
     * @param array   $criteria
     * @param integer $page
     * @param integer $maxPerPage
     *
     * @return \Sonata\DatagridBundle\Pager\PagerInterface
     */
    public function getPager(array $criteria, $page, $maxPerPage = 10);
}
