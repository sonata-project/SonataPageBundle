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
use Sonata\AdminBundle\Validator\ErrorElement;

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
        } else if ($settings['pageId']){
            $page = $settings['pageId'];
        } else {
            $page = $this->manager->getPage('/');
        }

        return $this->renderResponse('SonataPageBundle:Block:block_core_children_pages.html.twig', array(
            'page'     => $page,
            'block'    => $block,
            'settings' => $settings
        ), $response);
    }

    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->addType('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('title', 'text', array(
                  'required' => false
                )),
                array('current', 'checkbox', array(
                  'required' => false
                )),
                array('pageId', 'sonata_page_parent_selector', array(
                    'model_manager' => $formMapper->getAdmin()->getModelManager(),
                    'class'         => 'Application\Sonata\PageBundle\Entity\Page',
                    'required'      => false
                )),
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

    public function prePersist(BlockInterface $block)
    {
        $block->setSetting('pageId', is_object($block->getSetting('pageId')) ? $block->getSetting('pageId')->getId() : null);
    }

    public function preUpdate(BlockInterface $block)
    {
        $block->setSetting('pageId', is_object($block->getSetting('pageId')) ? $block->getSetting('pageId')->getId() : null);
    }

    public function load(BlockInterface $block)
    {
        if (is_numeric($block->getSetting('pageId'))) {
            $block->setSetting('pageId', $this->manager->getPage($block->getSetting('pageId')));
        }
    }
}