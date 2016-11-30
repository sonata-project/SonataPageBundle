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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface SiteRequestContextInterface
{
    /**
     * {@inheritdoc}
     */
    public function getHost();

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl();

    public function setSite(SiteInterface $site);

    public function getSite();
}
