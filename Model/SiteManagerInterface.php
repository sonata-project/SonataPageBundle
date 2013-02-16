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

/**
 * SiteManagerInterface
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface SiteManagerInterface
{
    /**
     * @param array $criteria
     *
     * @return SiteInterface
     */
    public function findOneBy(array $criteria = array());

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findBy(array $criteria = array());

    /**
     * @param SiteInterface $object
     *
     * @return void
     */
    public function save(SiteInterface $object);

    /**
     * @return SiteInterface
     */
    public function create();

    /**
     * @return string
     */
    public function getClass();
}
