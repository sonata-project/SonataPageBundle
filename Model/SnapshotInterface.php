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
 * Defines methods to interact with the persistency layer of a SnapshotInterface
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface SnapshotInterface
{
    /**
     * Set routeName
     *
     * @param string $routeName
     */
    public function setRouteName($routeName);

    /**
     * Get routeName
     *
     * @return string $routeName
     */
    public function getRouteName();

    /**
     * Get routeAlias
     *
     * @return string $routeAlias
     */
    public function getPageAlias();

    /**
     * The route alias defines an internal url code that user can use to point
     * to an url. This feature must used with care to avoid to many generated queries
     *
     * Set pageAlias
     *
     * @param string $pageAlias
     */
    public function setPageAlias($pageAlias);

    /**
     * Returns the page type
     *
     * @return string
     */
    public function getType();

    /**
     * Sets the page type
     *
     * @param string $type
     */
    public function setType($type);

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
     * Set name
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName();

    /**
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url);

    /**
     * Get url
     *
     * @return string $url
     */
    public function getUrl();

    /**
     * Set publicationDateStart
     *
     * @param \DateTime $publicationDateStart
     */
    public function setPublicationDateStart(\DateTime $publicationDateStart = null);

    /**
     * Get publicationDateStart
     *
     * @return \DateTime $publicationDateStart
     */
    public function getPublicationDateStart();

    /**
     * Set publicationDateEnd
     *
     * @param \DateTime $publicationDateEnd
     */
    public function setPublicationDateEnd(\DateTime $publicationDateEnd = null);

    /**
     * Get publicationDateEnd
     *
     * @return \DateTime $publicationDateEnd
     */
    public function getPublicationDateEnd();

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * Get createdAt
     *
     * @return \DateTime $createdAt
     */
    public function getCreatedAt();

    /**
     * Set updatedAt
     *
     * @param \Datetime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * Get updatedAt
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
     * return bool
     */
    public function isHybrid();

    /**
     * @param integer $position
     */
    public function setPosition($position);

    /**
     * @return integer
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

    /**
     * @param SiteInterface $site
     */
    public function setSite(SiteInterface $site);

    /**
     * @return SiteInterface
     */
    public function getSite();

    /**
     * Serialized data of the current page
     *
     * @return string
     */
    public function getContent();
}
