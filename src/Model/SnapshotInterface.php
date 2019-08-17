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
 * @method int|null           getTargetId()
 * @method void               setTargetId(?int $targetId)
 * @method PageInterface|null getTarget()
 * @method void               setTarget(?PageInterface $target)
 * @method int|null           getParent()
 * @method void               setParentId(?int $parentId)
 */
interface SnapshotInterface
{
    /**
     * Set routeName.
     *
     * @param string $routeName
     */
    public function setRouteName($routeName);

    /**
     * Get routeName.
     *
     * @return string $routeName
     */
    public function getRouteName();

    /**
     * Get routeAlias.
     *
     * @return string|null $routeAlias
     */
    public function getPageAlias();

    /**
     * The route alias defines an internal url code that user can use to point
     * to an url. This feature must used with care to avoid to many generated queries.
     *
     * Set pageAlias
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
     * Set enabled.
     *
     * @param bool $enabled
     */
    public function setEnabled($enabled);

    /**
     * Get enabled.
     *
     * @return bool $enabled
     */
    public function getEnabled();

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Get name.
     *
     * @return string $name
     */
    public function getName();

    /**
     * Set url.
     *
     * @param string|null $url
     */
    public function setUrl($url);

    /**
     * Get url.
     *
     * @return string|null $url
     */
    public function getUrl();

    /**
     * Set publicationDateStart.
     */
    public function setPublicationDateStart(\DateTime $publicationDateStart = null);

    /**
     * Get publicationDateStart.
     *
     * @return \DateTime|null $publicationDateStart
     */
    public function getPublicationDateStart();

    /**
     * Set publicationDateEnd.
     */
    public function setPublicationDateEnd(\DateTime $publicationDateEnd = null);

    /**
     * Get publicationDateEnd.
     *
     * @return \DateTime|null $publicationDateEnd
     */
    public function getPublicationDateEnd();

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * Get createdAt.
     *
     * @return \DateTime $createdAt
     */
    public function getCreatedAt();

    /**
     * Set updatedAt.
     *
     * @param \Datetime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * Get updatedAt.
     *
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
    public function setPage(PageInterface $page = null);

    /**
     * @return PageInterface
     */
    public function getPage();

    public function setSite(SiteInterface $site);

    /**
     * @return SiteInterface
     */
    public function getSite();

    /**
     * Serialized data of the current page.
     *
     * @return array
     */
    public function getContent();
}
