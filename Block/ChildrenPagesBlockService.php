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
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\PageBundle\Exception\PageNotFoundException;

/**
 * PageExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ChildrenPagesBlockService extends BaseBlockService
{
    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerInterface $manager
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     * @return string
     */
    public function execute(CmsManagerInterface $manager, BlockInterface $block, PageInterface $page, Response $response = null)
    {
        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());

        if ($settings['current']) {
            $page = $manager->getCurrentPage();
        } else if ($settings['pageId']) {
            $page = $settings['pageId'];
        } else {
            try {
                $page = $manager->getPage($page->getSite(), '/');
            } catch (PageNotFoundException $e) {
                $page = false;
            }

        }

        return $this->renderResponse('SonataPageBundle:Block:block_core_children_pages.html.twig', array(
            'page'     => $page,
            'block'    => $block,
            'settings' => $settings
        ), $response);
    }

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerInterface $manager
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    public function validateBlock(CmsManagerInterface $manager, ErrorElement $errorElement, BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerInterface $manager
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    public function buildEditForm(CmsManagerInterface $manager, FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('title', 'text', array(
                  'required' => false
                )),
                array('current', 'checkbox', array(
                  'required' => false
                )),
                array('pageId', 'sonata_page_selector', array(
                    'model_manager' => $formMapper->getAdmin()->getModelManager(),
                    'class'         => 'Application\Sonata\PageBundle\Entity\Page',
                    'required'      => false
                )),
                array('class', 'text', array(
                  'required' => false
                )),
            )
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Children Page (core)';
    }

    /**
     * Returns the default options link to the service
     *
     * @return array
     */
    public function getDefaultSettings()
    {
        return array(
            'current' => true,
            'pageId'  => null,
            'title'   => '',
            'class'   => '',
        );
    }

    /**
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    public function prePersist(BlockInterface $block)
    {
        $block->setSetting('pageId', is_object($block->getSetting('pageId')) ? $block->getSetting('pageId')->getId() : null);
    }

    /**
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    public function preUpdate(BlockInterface $block)
    {
        $block->setSetting('pageId', is_object($block->getSetting('pageId')) ? $block->getSetting('pageId')->getId() : null);
    }

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerInterface $manager
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    public function load(CmsManagerInterface $manager, BlockInterface $block)
    {
        if (is_numeric($block->getSetting('pageId'))) {
            $block->setSetting('pageId', $manager->getPage($block->getSetting('pageId')));
        }
    }
}