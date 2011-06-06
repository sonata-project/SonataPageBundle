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


interface PageManagerInterface
{
    /**
     * return a page with the given routeName
     *
     * @param string $routeName
     * @return PageInterface|false
     */
    function getPageByName($routeName);

    function findOneBy(array $criteria = array());

    function findBy(array $criteria = array());

    /**
     * return a page with the give slug
     *
     * @param string $slug
     * @return PageInterface
     */
    function getPageBySlug($slug);

    function getDefaultTemplate();

    /**
     * @abstract
     * @param PageInterface $object
     * @return void
     */
    function save(PageInterface $object);

    /**
     * @abstract
     * @param array $params
     * @return void
     */
    function createNewPage(array $params = array());
}