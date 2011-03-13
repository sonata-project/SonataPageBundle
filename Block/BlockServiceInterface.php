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

interface BlockServiceInterface
{

    /**
     * @abstract
     * @param \Symfony\Component\Form\Form $form
     * @param BlockInterface $block
     * @return void
     */
    function defineBlockForm(Form $form, BlockInterface $block);

    /**
     * @abstract
     * @param BlockInterface $block
     * @param  $page
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     * @return void
     */
    function execute(BlockInterface $block, $page, Response $response = null);

    /**
     * @abstract
     * @param  $view
     * @param array $parameters
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     * @return void
     */
    function render($view, array $parameters = array(), Response $response = null);

    /**
     * @abstract
     * @param BlockInterface $block
     * @return void
     */
    function validateBlock(BlockInterface $block);
}