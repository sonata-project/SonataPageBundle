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

use Sonata\BlockBundle\Model\BlockInterface;

interface PageBlockInterface extends BlockInterface
{
    /**
     * @return PageInterface
     */
    public function getPage();

    /**
     * @param PageInterface $page The related page
     *
     * @return mixed
     */
    public function setPage(PageInterface $page = null);
}
