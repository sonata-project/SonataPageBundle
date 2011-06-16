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

/**
 * PageExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ChildrenPagesBlockService extends BaseBlockService
{
    public function execute(BlockInterface $block, PageInterface $page, Response $response = null)
    {
        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());

        if ($settings['current']) {
            $page = $this->manager->getCurrentPage();
        } else {
            $page = $this->manager->getPageById($settings['pageId']);
        }

        return $this->renderResponse('SonataPageBundle:Block:block_core_children_pages.html.twig', array(
            'page'     => $page,
            'block'    => $block,
            'settings' => $settings
        ), $response);
    }

    public function validateBlock(BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    public function buildCreateForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->addType('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('title', 'string', array()),
                array('current', 'checkbox', array()),
                array('pageId', 'textarea', array()),
            )
        ));
    }

    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->addType('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('title', 'string', array()),
                array('current', 'checkbox', array()),
                array('pageId', 'textarea', array()),
            )
        ));
    }

    public function getName()
    {
        return 'Children Page (core)';
    }

    /**
     * Returns the default options link to the service
     *
     * @return array
     */
    function getDefaultSettings()
    {
        return array(
            'current' => true,
            'pageId'  => null,
            'title'   => ''
        );
    }
}