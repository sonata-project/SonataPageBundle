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

    /**
     * @abstract
     * @param array $criteria
     * @return PageInterface
     */
    function findOneBy(array $criteria = array());

    /**
     * @abstract
     * @param array $criteria
     * @return array
     */
    function findBy(array $criteria = array());

    /**
     * return a page with the give slug
     *
     * @param string $url
     * @return PageInterface
     */
    function getPageByUrl($url);

    /**
     * Returns a string, the code name of the template
     * @abstract
     * @return string
     */
    function setDefaultTemplateCode($code);

    /**
     * @abstract
     * @param PageInterface $object
     * @return void
     */
    function save(PageInterface $object);

    /**
     * @abstract
     * @param array $params
     * @return PageInterface
     */
    function createNewPage(array $params = array());

    /**
     * @abstract
     * @return array
     */
    function getTemplates();

    /**
     * @abstract
     * @param string $code template code
     * @return Template
     */
    function getTemplate($code);
}