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
 * SiteInterface
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface SiteInterface
{
    public function getId();

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $host
     *
     * @return void
     */
    public function setHost($host);

    /**
     * @return string
     */
    public function getHost();

    /**
     * @return string
     */
    public function getLocale();

    /**
     * @param string $locale
     *
     * @return void
     */
    public function setLocale($locale);

    /**
     * @return \DateTime
     */
    public function getEnabledFrom();

    /**
     * @param \DateTime|null $enabledFrom
     *
     * @return void
     */
    public function setEnabledFrom(\DateTime $enabledFrom = null);

    /**
     * @return \DateTime
     */
    public function getEnabledTo();

    /**
     * @param \DateTime|null $enabledTo
     *
     * @return void
     */
    public function setEnabledTo(\DateTime $enabledTo = null);

    /**
     * @return boolean
     */
    public function getIsDefault();

    /**
     * @param boolean $default
     *
     * @return void
     */
    public function setIsDefault($default);

    /**
     * @param string $path
     *
     * @return void
     */
    public function setRelativePath($path);

    /**
     * @return string
     */
    public function getRelativePath();

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled);

    /**
     * Get enabled
     *
     * @return boolean $enabled
     */
    public function getEnabled();

    /**
     * Returns TRUE whether the site is enabled
     *
     * @return boolean
     */
    public function isEnabled();

    /**
     * Set createdAt
     *
     * @param \Datetime|null $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * Get createdAt
     *
     * @return \Datetime $createdAt
     */
    public function getCreatedAt();

    /**
     * Set updatedAt
     *
     * @param \Datetime|null $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * Get updatedAt
     *
     * @return \Datetime $updatedAt
     */
    public function getUpdatedAt();

    /**
     * @return string
     */
    public function __toString();

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return boolean
     */
    public function isLocalhost();

    /**
     * @param string $metaDescription
     *
     * @return string
     */
    public function setMetaDescription($metaDescription);

    /**
     * @return string
     */
    public function getMetaDescription();

    /**
     * @param string $metaKeywords
     *
     * @return string
     */
    public function setMetaKeywords($metaKeywords);

    /**
     * @return string
     */
    public function getMetaKeywords();

    /**
     * @param string $title
     *
     * @return string
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getTitle();
}
