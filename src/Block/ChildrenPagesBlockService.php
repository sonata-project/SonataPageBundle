<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Block;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractAdminBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Templating\EngineInterface;

/**
 * Render children pages.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ChildrenPagesBlockService extends AbstractAdminBlockService
{
    /**
     * @var SiteSelectorInterface
     */
    protected $siteSelector;

    /**
     * @var CmsManagerSelectorInterface
     */
    protected $cmsManagerSelector;

    /**
     * @param string                      $name
     * @param EngineInterface             $templating
     * @param SiteSelectorInterface       $siteSelector
     * @param CmsManagerSelectorInterface $cmsManagerSelector
     */
    public function __construct($name, EngineInterface $templating, SiteSelectorInterface $siteSelector, CmsManagerSelectorInterface $cmsManagerSelector)
    {
        parent::__construct($name, $templating);

        $this->siteSelector = $siteSelector;
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

        return $this->renderResponse($blockContext->getTemplate(), [
            'page' => $page,
            'block' => $blockContext->getBlock(),
            'settings' => $settings,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', [
            'keys' => [
                ['title', 'text', [
                  'required' => false,
                    'label' => 'form.label_title',
                ]],
                ['current', 'checkbox', [
                  'required' => false,
                  'label' => 'form.label_current',
                ]],
                ['pageId', 'sonata_page_selector', [
                    'model_manager' => $formMapper->getAdmin()->getModelManager(),
                    'class' => $formMapper->getAdmin()->getClass(),
                    'site' => $block->getPage()->getSite(),
                    'required' => false,
                    'label' => 'form.label_page',
                ]],
                ['class', 'text', [
                  'required' => false,
                  'label' => 'form.label_class',
                ]],
            ],
            'translation_domain' => 'SonataPageBundle',
        ]);
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
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'current' => true,
            'pageId' => null,
            'title' => '',
            'class' => '',
            'template' => 'SonataPageBundle:Block:block_core_children_pages.html.twig',
        ]);
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
            $site = $block->getPage()->getSite();

            $block->setSetting('pageId', $cmsManager->getPage($site, $block->getSetting('pageId')));
        }
    }
}
