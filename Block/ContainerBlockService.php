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
use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Symfony\Bundle\FrameworkBundle\Util\Mustache;

/**
 * PageExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ContainerBlockService extends BaseBlockService
{

    /**
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return string
     */
    public function execute(BlockInterface $block, PageInterface $page, Response $response = null)
    {
        $content = $this->getTemplating()->render('SonataPageBundle:Page:renderContainer', array(
            'attributes' => array(
                'name'              => $block->getSetting('name'),
                'page'              => $page,
                'parent_container'  => $block
            ),
        ));

        return new Response(Mustache::renderString($block->getSetting('layout'), array(
            'CONTENT' => $content
        )));
    }

    /**
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    public function validateBlock(BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('children', array(), array(
            'edit'     => 'inline',
            'sortable' => 'position',
            'inline'   => 'table'
        ));
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    public function buildCreateForm(FormMapper $formMapper, BlockInterface $block)
    {

    }

    public function getName()
    {
        return 'Container (core)';
    }

    /**
     * Returns the default options link to the service
     *
     * @return array
     */
    function getDefaultSettings()
    {
        return array('layout' => '{{ CONTENT }}');
    }
}