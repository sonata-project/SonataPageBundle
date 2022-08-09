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

namespace Sonata\PageBundle\Request;

use Sonata\PageBundle\Model\SiteInterface;

/**
 * @author Joeri Timmermans <info@pix-art.be>
 */
interface SiteRequestContextInterface
{
    public function setSite(?SiteInterface $site): void;

    public function getSite(): ?SiteInterface;
}
