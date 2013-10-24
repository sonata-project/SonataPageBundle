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

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Templating\EngineInterface;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;

use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;

/**
 * Render children pages
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ChildrenPagesBlockService extends BaseBlockService
{
    protected $siteSelector;

    protected $cmsManagerSelector;

    /**
     * @param string                                                    $name
     * @param \Symfony\Component\Templating\EngineInterface             $templating
     * @param \Sonata\PageBundle\Site\SiteSelectorInterface             $siteSelector
     * @param \Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface $cmsManagerSelector
     */
    public function __construct($name, EngineInterface $templating, SiteSelectorInterface $siteSelector, CmsManagerSelectorInterface $cmsManagerSelector)
    {
        parent::__construct($name, $templating);

        $this->siteSelector       = $siteSelector;
        $this->cmsManagerSelector = $cmsManagerSelector;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $settings = $blockContext->getSettings();

        $cmsManager = $this->cmsManagerSelector->retrieve();

        if ($settings['current']) {
            $page = $cmsManager->getCurrentPage();
        } elseif ($settings['pageId']) {
            $page = $settings['pageId'];
        } else {
            try {
                $page = $cmsManager->getPage($this->siteSelector->retrieve(), '/');
            } catch (PageNotFoundException $e) {
                $page = false;
            }
        }

        return $this->renderResponse($blockContext->getTemplate(), array(
            'page'     => $page,
            'block'    => $blockContext->getBlock(),
            'settings' => $settings
        ), $response);
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
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
                    'class'         => $formMapper->getAdmin()->getClass(),
                    'site'          => $block->getPage()->getSite(),
                    'required'      => false
                )),
                array('class', 'text', array(
                  'required' => false
                )),
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Children Page (core)';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'current'  => true,
            'pageId'   => null,
            'title'    => '',
            'class'    => '',
            'template' => 'SonataPageBundle:Block:block_core_children_pages.html.twig'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(BlockInterface $block)
    {
        $block->setSetting('pageId', is_object($block->getSetting('pageId')) ? $block->getSetting('pageId')->getId() : null);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(BlockInterface $block)
    {
        $block->setSetting('pageId', is_object($block->getSetting('pageId')) ? $block->getSetting('pageId')->getId() : null);
    }

    /**
     * {@inheritdoc}
     */
    public function load(BlockInterface $block)
    {
        if (is_numeric($block->getSetting('pageId', null))) {
            $cmsManager = $this->cmsManagerSelector->retrieve();
            $site       = $block->getPage()->getSite();

            $block->setSetting('pageId', $cmsManager->getPage($site, $block->getSetting('pageId')));
        }
    }
}
