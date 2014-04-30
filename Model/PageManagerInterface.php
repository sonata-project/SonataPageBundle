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
use Sonata\CoreBundle\Model\PageableManagerInterface;

/**
 * Defines methods to interact with the persistency layer of a PageInterface
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface PageManagerInterface extends ManagerInterface, PageableManagerInterface
{
    /**
     * Returns a page with the give slug
     *
     * @param SiteInterface $site
     * @param string        $url
     *
     * @return PageInterface
     */
    public function getPageByUrl(SiteInterface $site, $url);

    /**
     * Returns an array of Pages Entity where the id is the key
     *
     * @param SiteInterface $site
     *
     * @return array
     */
    public function loadPages(SiteInterface $site);

    /**
     * @param PageInterface $page
     *
     * @return mixed
     */
    public function fixUrl(PageInterface $page);
}
