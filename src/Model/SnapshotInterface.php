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
interface SnapshotInterface
{
    /**
     * @return int|string|null
     */
    public function getId();

    /**
     * @return string|null
     */
    public function getRouteName();

    /**
     * @param string|null $routeName
     *
     * @return void
     */
    public function setRouteName($routeName);

    /**
     * @return string|null
     */
    public function getPageAlias();

    /**
     * The route alias defines an internal url code that user can use to point
     * to an url. This feature must used with care to avoid to many generated queries.
     *
     * @param string|null $pageAlias
     *
     * @return void
     */
    public function setPageAlias($pageAlias);

    /**
     * @return string|null
     */
    public function getType();

    /**
     * @param string|null $type
     *
     * @return void
     */
    public function setType($type);

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
    public function getUrl();

    /**
     * @param string|null $url
     *
     * @return void
     */
    public function setUrl($url);

    /**
     * @return \DateTimeInterface|null
     */
    public function getPublicationDateStart();

    /**
     * @return void
     */
    public function setPublicationDateStart(?\DateTimeInterface $publicationDateStart = null);

    /**
     * @return \DateTimeInterface|null
     */
    public function getPublicationDateEnd();

    /**
     * @return void
     */
    public function setPublicationDateEnd(?\DateTimeInterface $publicationDateEnd = null);

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
     * @return bool
     */
    public function getDecorate();

    /**
     * @param bool $decorate
     *
     * @return void
     */
    public function setDecorate($decorate);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param int $position
     *
     * @return void
     */
    public function setPosition($position);

    /**
     * @return PageInterface|null
     */
    public function getPage();

    /**
     * @return void
     */
    public function setPage(?PageInterface $page = null);

    /**
     * @return SiteInterface|null
     */
    public function getSite();

    /**
     * @return void
     */
    public function setSite(?SiteInterface $site = null);

    /**
     * Serialized data of the current page.
     *
     * @return array
     */
    public function getContent();

    /**
     * @param array $content
     */
    public function setContent($content): void;

    /**
     * @return int|string|null
     */
    public function getParentId();

    /**
     * @param int|string|null $parentId
     */
    public function setParentId($parentId): void;

    /**
     * @return bool
     */
    public function isHybrid();
}
