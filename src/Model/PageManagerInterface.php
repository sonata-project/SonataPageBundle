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

namespace Sonata\PageBundle\Model;

use Sonata\Doctrine\Model\ManagerInterface;

/**
 * Defines methods to interact with the persistency layer of a PageInterface.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface PageManagerInterface extends ManagerInterface
{
    /**
     * Returns a page with the give slug.
     *
     * @param string $url
     *
     * @return PageInterface
     */
    public function getPageByUrl(SiteInterface $site, $url);

    /**
     * Returns an array of Pages Entity where the id is the key.
     *
     * @return array
     */
    public function loadPages(SiteInterface $site);

    /**
     * @return PageInterface[]
     */
    public function getHybridPages(SiteInterface $site);

    public function fixUrl(PageInterface $page);
}
