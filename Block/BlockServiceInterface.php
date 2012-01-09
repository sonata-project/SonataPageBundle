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
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\AdminBundle\Validator\ErrorElement;

interface BlockServiceInterface
{
    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerInterface $manager
     * @param \Sonata\AdminBundle\Form\FormMapper $form
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    function buildEditForm(CmsManagerInterface $manager, FormMapper $form, BlockInterface $block);

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerInterface $manager
     * @param \Sonata\AdminBundle\Form\FormMapper $form
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    function buildCreateForm(CmsManagerInterface $manager, FormMapper $form, BlockInterface $block);

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerInterface $manager
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     * @return void
     */
    function execute(CmsManagerInterface $manager, BlockInterface $block, PageInterface $page, Response $response = null);

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerInterface $manager
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    function validateBlock(CmsManagerInterface $manager, ErrorElement $errorElement, BlockInterface $block);

    /**
     * @return string
     */
    function getName();

    /**
     * Returns the default settings link to the service
     *
     * @return array
     */
    function getDefaultSettings();

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerInterface $manager
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    function getCacheElement(CmsManagerInterface $manager, BlockInterface $block);

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerInterface $manager
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    function load(CmsManagerInterface $manager, BlockInterface $block);

    /**
     * @param $media
     * @return array
     */
    function getJavacripts($media);

    /**
     * @param $media
     * @return array
     */
    function getStylesheets($media);
}