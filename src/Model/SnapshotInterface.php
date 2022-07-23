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
 * Defines methods to interact with the persistency layer of a SnapshotInterface.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @method int|null getId()
 * @method int|null getTargetId()               NEXT_MAJOR: Remove this line.
 * @method void     setTargetId(?int $targetId) NEXT_MAJOR: Remove this line.
 * @method PageInterface|null getTarget(): NEXT_MAJOR: Remove this line.
 * @method void     setTarget(?PageInterface $target) NEXT_MAJOR: Remove this line.
 * @method int|null getParent()
 * @method void     setParentId(?int $parentId)
 */
interface SnapshotInterface
{
    /**
     * @param string $routeName
     */
    public function setRouteName($routeName);

    /**
     * @return string $routeName
     */
    public function getRouteName();

    /**
     * @return string|null $routeAlias
     */
    public function getPageAlias();

    /**
     * The route alias defines an internal url code that user can use to point
     * to an url. This feature must used with care to avoid to many generated queries.
     *
     * @param string|null $pageAlias
     */
    public function setPageAlias($pageAlias);

    /**
     * Returns the page type.
     *
     * @return string|null
     */
    public function getType();

    /**
     * Sets the page type.
     *
     * @param string|null $type
     */
    public function setType($type);

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled);

    /**
     * @return bool $enabled
     */
    public function getEnabled();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string $name
     */
    public function getName();

    /**
     * @param string|null $url
     */
    public function setUrl($url);

    /**
     * @return string|null $url
     */
    public function getUrl();

    public function setPublicationDateStart(?\DateTime $publicationDateStart = null);

    /**
     * @return \DateTime|null $publicationDateStart
     */
    public function getPublicationDateStart();

    public function setPublicationDateEnd(?\DateTime $publicationDateEnd = null);

    /**
     * @return \DateTime|null $publicationDateEnd
     */
    public function getPublicationDateEnd();

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(?\DateTime $createdAt = null);

    /**
     * @return \DateTime $createdAt
     */
    public function getCreatedAt();

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(?\DateTime $updatedAt = null);

    /**
     * @return \DateTime $updatedAt
     */
    public function getUpdatedAt();

    /**
     * @param bool $decorate
     */
    public function setDecorate($decorate);

    /**
     * @return bool
     */
    public function getDecorate();

    /**
     * return bool.
     */
    public function isHybrid();

    /**
     * @param int $position
     */
    public function setPosition($position);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param PageInterface $page
     */
    public function setPage(?PageInterface $page = null);

    /**
     * @return PageInterface|null
     */
    public function getPage();

    public function setSite(?SiteInterface $site = null);

    /**
     * @return SiteInterface|null
     */
    public function getSite();

    /**
     * @param array $content
     */
    public function setContent($content): void;

    /**
     * Serialized data of the current page.
     *
     * @return array
     */
    public function getContent();
}
