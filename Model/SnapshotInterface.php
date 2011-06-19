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

    function setDecorate($decorate);

    function getDecorate();

    function isHybrid();

    function setPosition($position);

    function getPosition();
}