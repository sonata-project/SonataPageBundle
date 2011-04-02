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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;
use Sonata\AdminBundle\Form\FormMapper;
    
/**
 * PageExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ContainerBlockService extends BaseBlockService
{

    /**
     * @param BlockInterface $block
     * @param  $page
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     * @return
     */
    public function execute(BlockInterface $block, $page, Response $response = null)
    {

        return $this->getTemplating()->renderResponse('SonataPage:Page:renderContainer', array(
            'attributes' => array(
                'name'              => $block->getSetting('name'),
                'page'              => $page,
                'parent_container'  => $block
            ),
        ));
    }

    /**
     * @param BlockInterface $block
     * @return void
     */
    public function validateBlock(BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    /**
     * @param \Symfony\Component\Form\Form $form
     * @param BlockInterface $block
     * @return void
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        
    }

    /**
     * @param \Symfony\Component\Form\Form $form
     * @param BlockInterface $block
     * @return void
     */
    public function buildCreateForm(FormMapper $formMapper, BlockInterface $block)
    {

    }
}