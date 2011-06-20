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

interface BlockInterface
{

    function setId($id);

    function getId();

    /**
     * Set type
     *
     * @param string $type
     */
    function setType($type);

    /**
     * Get type
     *
     * @return string $type
     */
    function getType();

    /**
     * Set settings
     *
     * @param array $settings
     */
    function setSettings(array $settings = array());

    /**
     * Get settings
     *
     * @return array $settings
     */
    function getSettings();

    function setSetting($name, $value);

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
     * Set position
     *
     * @param integer $position
     */
    function setPosition($position);

    /**
     * Get position
     *
     * @return integer $position
     */
    function getPosition();

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
     * Add children
     *
     * @param BlockInterface $children
     */
    function addChildren(BlockInterface $children);

    /**
     * Get children
     *
     * @return Doctrine\Common\Collections\Collection $children
     */
    function getChildren();

    /**
     * Set parent
     *
     * @param BlockInterface $parent
     */
    function setParent(BlockInterface $parent);

    /**
     * Get parent
     *
     * @return BlockInterface $parent
     */
    function getParent();

    /**
     * Set page
     *
     * @param PageInterface $page
     */
    function setPage(PageInterface $page);

    /**
     * Get page
     *
     * @return PageInterface $page
     */
    function getPage();

    /**
     * @abstract
     * @param string $name
     * @param null $default
     * @return mixed
     */
    function getSetting($name, $default = null);

    /**
     * @abstract
     * @return void
     */
    function hasParent();

    /**
     * @abstract
     * @return integer
     */
    function getTtl();

    function __toString();
}