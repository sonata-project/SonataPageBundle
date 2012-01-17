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

interface SiteInterface
{
    function getId();

    /**
     * @param $name
     * @return void
     */
    function setName($name);

    /**
     * @return void
     */
    function getName();

    /**
     * @param $domain
     * @return void
     */
    function setDomain($domain);

    /**
     * @return void
     */
    function getDomain();

    /**
     * @return \DateTime
     */
    function getEnabledFrom();

    /**
     * @param \DateTime $enabledFrom
     * @return void
     */
    function setEnabledFrom(\DateTime $enabledFrom);

    /**
     * @return \DateTime
     */
    function getEnabledTo();

    /**
     * @param \DateTime $enabledFrom
     * @return void
     */
    function setEnabledTo(\DateTime $enabledFrom);

    /**
     * @return boolean
     */
    function getIsDefault();

    /**
     * @param boolean $default
     * @return void
     */
    function setIsDefault($default);

    /**
     * @param $path
     * @return void
     */
    function setRelativePath($path);

    /**
     * @return void
     */
    function getRelativePath();

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    function setEnabled($enabled);

    /**
     * Get enabled
     *
     * @return boolean $enabled
     */
    function getEnabled();

    /**
     * Set createdAt
     *
     * @param \Datetime $createdAt
     */
    function setCreatedAt(\DateTime $createdAt = null);

    /**
     * Get createdAt
     *
     * @return \Datetime $createdAt
     */
    function getCreatedAt();

    /**
     * Set updatedAt
     *
     * @param \Datetime $updatedAt
     */
    function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * Get updatedAt
     *
     * @return \Datetime $updatedAt
     */
    function getUpdatedAt();

    /**
     * @return string
     */
    function __toString();

    /**
     * @return string
     */
    function getUrl();
}