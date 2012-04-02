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

interface SnapshotInterface
{
    /**
     * Set routeName
     *
     * @param string $routeName
     */
    function setRouteName($routeName);

    /**
     * Get routeName
     *
     * @return string $routeName
     */
    function getRouteName();

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
     * Set showInMenu
     *
     * @param boolean $showInMenu
     */
    function setShowInMenu($showInMenu);

    /**
     * Get showInMenu
     *
     * @return boolean $showInMenu
     */
    function getShowInMenu();

    /**
     * Set name
     *
     * @param string $name
     */
    function setName($name);

    /**
     * Get name
     *
     * @return string $name
     */
    function getName();

    /**
     * Set url
     *
     * @param string $url
     */
    function setUrl($url);

    /**
     * Get url
     *
     * @return string $url
     */
    function getUrl();

    /**
     * Set publicationDateStart
     *
     * @param \DateTime $publicationDateStart
     */
    function setPublicationDateStart(\DateTime $publicationDateStart = null);

    /**
     * Get publicationDateStart
     *
     * @return \DateTime $publicationDateStart
     */
    function getPublicationDateStart();

    /**
     * Set publicationDateEnd
     *
     * @param \DateTime $publicationDateEnd
     */
    function setPublicationDateEnd(\DateTime $publicationDateEnd = null);

    /**
     * Get publicationDateEnd
     *
     * @return \DateTime $publicationDateEnd
     */
    function getPublicationDateEnd();

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     */
    function setCreatedAt(\DateTime $createdAt = null);

    /**
     * Get createdAt
     *
     * @return \DateTime $createdAt
     */
    function getCreatedAt();

    /**
     * Set updatedAt
     *
     * @param datetime $updatedAt
     */
    function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * Get updatedAt
     *
     * @return \DateTime $updatedAt
     */
    function getUpdatedAt();

    /**
     * @abstract
     * @param bool $decorate
     */
    function setDecorate($decorate);

    /**
     * @abstract
     * @return bool
     */
    function getDecorate();

    /**
     * @abstract
     * return bool
     */
    function isHybrid();

    /**
     * @abstract
     * @param integer $position
     */
    function setPosition($position);

    /**
     * @abstract
     * @return integer
     */
    function getPosition();

    /**
     * @abstract
     * @param PageInterface $page
     */
    function setPage(PageInterface $page = null);

    /**
     * @abstract
     * @return PageInterface
     */
    function getPage();

    /**
     *
     * @param SiteInterface $site
     * @return void
     */
    function setSite(SiteInterface $site);

    /**
     *
     * @return void
     */
    function getSite();
}