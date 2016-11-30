<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Request;

use Sonata\PageBundle\Model\SiteInterface;

/**
 * SiteRequestContext.
 *
 * @author Joeri Timmermans <info@pix-art.be>
 */
interface SiteRequestContextInterface
{
    /**
     * @param SiteInterface $site
     */
    public function setSite(SiteInterface $site);

    /**
     * @return SiteInterface|null
     */
    public function getSite();
}
