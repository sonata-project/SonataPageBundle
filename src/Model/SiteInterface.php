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

/**
 * SiteInterface.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface SiteInterface
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $host
     */
    public function setHost($host);

    /**
     * @return string
     */
    public function getHost();

    /**
     * @return string|null
     */
    public function getLocale();

    /**
     * @param string|null $locale
     */
    public function setLocale($locale);

    /**
     * @return \DateTime|null
     */
    public function getEnabledFrom();

    public function setEnabledFrom(\DateTime $enabledFrom = null);

    /**
     * @return \DateTime|null
     */
    public function getEnabledTo();

    public function setEnabledTo(\DateTime $enabledTo = null);

    /**
     * @return bool
     */
    public function getIsDefault();

    /**
     * @param bool $default
     */
    public function setIsDefault($default);

    /**
     * @param string|null $path
     */
    public function setRelativePath($path);

    /**
     * @return string|null
     */
    public function getRelativePath();

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled);

    /**
     * @return bool $enabled
     */
    public function getEnabled();

    /**
     * Returns TRUE whether the site is enabled.
     *
     * @return bool
     */
    public function isEnabled();

    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * @return \Datetime $createdAt
     */
    public function getCreatedAt();

    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * @return \Datetime $updatedAt
     */
    public function getUpdatedAt();

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return bool
     */
    public function isLocalhost();

    /**
     * @param string|null $metaDescription
     *
     * @return string
     */
    public function setMetaDescription($metaDescription);

    /**
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * @param string|null $metaKeywords
     *
     * @return string
     */
    public function setMetaKeywords($metaKeywords);

    /**
     * @return string|null
     */
    public function getMetaKeywords();

    /**
     * @param string|null $title
     *
     * @return string
     */
    public function setTitle($title);

    /**
     * @return string|null
     */
    public function getTitle();
}
