<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\PageBundle\Block;

use Symfony\Component\Form\Form;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;

interface BlockServiceInterface
{
    /**
     * @abstract
     * @param \Sonata\AdminBundle\Form\FormMapper $form
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    function buildEditForm(FormMapper $form, BlockInterface $block);

    /**
     * @abstract
     * @param \Sonata\AdminBundle\Form\FormMapper $form
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    function buildCreateForm(FormMapper $form, BlockInterface $block);

    /**
     * @abstract
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return void
     */
    function execute(BlockInterface $block, PageInterface $page, Response $response = null);

    /**
     * @abstract
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    function validateBlock(BlockInterface $block);

    /**
     * @abstract
     * @return string
     */
    function getName();

    /**
     * Returns the default settings link to the service
     *
     * @abstract
     * @return array
     */
    function getDefaultSettings();

    /**
     * @abstract
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return \Sonata\PageBundle\Cache\CacheElement
     */
    function getCacheElement(BlockInterface $block);
}