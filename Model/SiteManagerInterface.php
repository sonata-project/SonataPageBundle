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
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface SiteManagerInterface
{
    /**
     * @param array $criteria
     *
     * @return SiteInterface
     */
    function findOneBy(array $criteria = array());

    /**
     * @param array $criteria
     *
     * @return array
     */
    function findBy(array $criteria = array());

    /**
     * @param SiteInterface $object
     *
     * @return void
     */
    function save(SiteInterface $object);

    /**
     * @return SiteInterface
     */
    function create();

    /**
     * @return string
     */
    function getClass();
}