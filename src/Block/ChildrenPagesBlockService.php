<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Block;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Form\Type\PageSelectorType;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Render children pages.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class ChildrenPagesBlockService extends AbstractBlockService implements EditableBlockService
{
    private SiteSelectorInterface $siteSelector;

    private CmsManagerSelectorInterface $cmsManagerSelector;

    /**
     * @var AdminInterface<PageInterface>
     */
    private AdminInterface $pageAdmin;

    /**
     * @param AdminInterface<PageInterface> $pageAdmin
     */
    public function __construct(
        Environment $twig,
        SiteSelectorInterface $siteSelector,
        CmsManagerSelectorInterface $cmsManagerSelector,
        AdminInterface $pageAdmin
    ) {
        parent::__construct($twig);

        $this->siteSelector = $siteSelector;
        $this->cmsManagerSelector = $cmsManagerSelector;
        $this->pageAdmin = $pageAdmin;
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $settings = $blockContext->getSettings();

        $cmsManager = $this->cmsManagerSelector->retrieve();

        if (null !== $settings['current']) {
            $page = $cmsManager->getCurrentPage();
        } elseif (null !== $settings['pageId']) {
            $page = $settings['pageId'];
        } else {
            $page = false;
            try {
                $site = $this->siteSelector->retrieve();
                if (null !== $site) {
                    $page = $cmsManager->getPage($site, '/');
                }
            } catch (PageNotFoundException $e) {
            }
        }

        $template = $blockContext->getTemplate();
        \assert(null !== $template);

        return $this->renderResponse($template, [
            'page' => $page,
            'block' => $blockContext->getBlock(),
            'settings' => $settings,
        ], $response);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('Children Page (core)', null, null, 'SonataPageBundle', [
            'class' => 'fa fa-home',
        ]);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        if (!$block instanceof PageBlockInterface) {
            return;
        }

        $page = $block->getPage();
        $site = null !== $page ? $page->getSite() : null;

        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [
                ['title', TextType::class, [
                  'required' => false,
                    'label' => 'form.label_title',
                ]],
                ['translation_domain', TextType::class, [
                    'label' => 'form.label_translation_domain',
                    'required' => false,
                ]],
                ['icon', TextType::class, [
                    'label' => 'form.label_icon',
                    'required' => false,
                ]],
                ['current', CheckboxType::class, [
                  'required' => false,
                  'label' => 'form.label_current',
                ]],
                ['pageId', PageSelectorType::class, [
                    'model_manager' => $this->pageAdmin->getModelManager(),
                    'class' => $this->pageAdmin->getClass(),
                    'site' => $site,
                    'required' => false,
                    'label' => 'form.label_page',
                ]],
                ['class', TextType::class, [
                  'required' => false,
                  'label' => 'form.label_class',
                ]],
            ],
            'translation_domain' => 'SonataPageBundle',
        ]);
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'current' => true,
            'pageId' => null,
            'title' => null,
            'translation_domain' => null,
            'icon' => null,
            'class' => null,
            'template' => '@SonataPage/Block/block_core_children_pages.html.twig',
        ]);
    }

    public function prePersist(BlockInterface $block): void
    {
        $page = $block->getSetting('pageId');

        $block->setSetting('pageId', $page instanceof PageInterface ? $page->getId() : null);
    }

    public function preUpdate(BlockInterface $block): void
    {
        $page = $block->getSetting('pageId');

        $block->setSetting('pageId', $page instanceof PageInterface ? $page->getId() : null);
    }

    public function load(BlockInterface $block): void
    {
        if (!$block instanceof PageBlockInterface) {
            return;
        }

        $pageId = $block->getSetting('pageId', null);

        if (null !== $pageId && !$pageId instanceof PageInterface) {
            $cmsManager = $this->cmsManagerSelector->retrieve();

            $page = $block->getPage();
            $site = null !== $page ? $page->getSite() : null;

            if (null !== $site) {
                $block->setSetting('pageId', $cmsManager->getPage($site, $pageId));
            }
        }
    }
}
