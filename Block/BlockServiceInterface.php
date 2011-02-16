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
    public function __construct($name, ContainerInterface $container);

    public function defineBlockForm(Form $form, BlockInterface $block);

    public function execute(BlockInterface $block, $page, Response $response = null);

    public function render($view, array $parameters = array(), Response $response = null);
}