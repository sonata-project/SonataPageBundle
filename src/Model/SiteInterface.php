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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface SiteInterface
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @return int|string|null
     */
    public function getId();

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string|null $name
     *
     * @return void
     */
    public function setName($name);

    /**
     * @return string|null
     */
    public function getTitle();

    /**
     * @param string|null $title
     *
     * @return void
     */
    public function setTitle($title);

    /**
     * @return string|null
     */
    public function getHost();

    /**
     * @param string|null $host
     *
     * @return void
     */
    public function setHost($host);

    /**
     * @return string|null
     */
    public function getLocale();

    /**
     * @param string|null $locale
     *
     * @return void
     */
    public function setLocale($locale);

    /**
     * @return \DateTimeInterface|null
     */
    public function getEnabledFrom();

    /**
     * @return void
     */
    public function setEnabledFrom(?\DateTimeInterface $enabledFrom = null);

    /**
     * @return \DateTimeInterface|null
     */
    public function getEnabledTo();

    /**
     * @return void
     */
    public function setEnabledTo(?\DateTimeInterface $enabledTo = null);

    /**
     * @return bool
     */
    public function getIsDefault();

    /**
     * @param bool $default
     *
     * @return void
     */
    public function setIsDefault($default);

    /**
     * @return string|null
     */
    public function getRelativePath();

    /**
     * @param string|null $path
     *
     * @return void
     */
    public function setRelativePath($path);

    /**
     * @return bool $enabled
     */
    public function getEnabled();

    /**
     * @param bool $enabled
     *
     * @return void
     */
    public function setEnabled($enabled);

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt();

    /**
     * @return void
     */
    public function setCreatedAt(?\DateTimeInterface $createdAt = null);

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt();

    /**
     * @return void
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt = null);

    /**
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * @param string|null $metaDescription
     *
     * @return void
     */
    public function setMetaDescription($metaDescription);

    /**
     * @return string|null
     */
    public function getMetaKeywords();

    /**
     * @param string|null $metaKeywords
     *
     * @return void
     */
    public function setMetaKeywords($metaKeywords);

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @return bool
     */
    public function isLocalhost();
}
