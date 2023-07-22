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
use Sonata\AdminBundle\Form\FormMapper as AdminFormMapper;
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
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
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
    /**
     * @param AdminInterface<PageInterface> $pageAdmin
     */
    public function __construct(
        Environment $twig,
        private SiteSelectorInterface $siteSelector,
        private CmsManagerSelectorInterface $cmsManagerSelector,
        private AdminInterface $pageAdmin
    ) {
        parent::__construct($twig);
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
            } catch (PageNotFoundException) {
            }
        }

        $template = $blockContext->getTemplate();

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

        if (!$form instanceof AdminFormMapper) {
            throw new \InvalidArgumentException('Children Pages Block requires to be used in the Admin context');
        }

        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [
                ['title', TextType::class, [
                    'required' => false,
                    'label' => 'form.label_title',
                    'translation_domain' => 'SonataPageBundle',
                ]],
                ['translation_domain', TextType::class, [
                    'label' => 'form.label_translation_domain',
                    'translation_domain' => 'SonataPageBundle',
                    'required' => false,
                ]],
                ['icon', TextType::class, [
                    'label' => 'form.label_icon',
                    'translation_domain' => 'SonataPageBundle',
                    'required' => false,
                ]],
                ['current', CheckboxType::class, [
                    'required' => false,
                    'label' => 'form.label_current',
                    'translation_domain' => 'SonataPageBundle',
                ]],
                $this->getPageSelectorBuilder($form, $block),
                ['class', TextType::class, [
                    'required' => false,
                    'label' => 'form.label_class',
                    'translation_domain' => 'SonataPageBundle',
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

    public function load(BlockInterface $block): void
    {
        $pageId = $block->getSetting('pageId', null);

        if (!$pageId instanceof PageInterface) {
            $block->setSetting('pageId', $this->pageAdmin->getObject($pageId));
        }
    }

    /**
     * @param AdminFormMapper<object> $form
     */
    private function getPageSelectorBuilder(AdminFormMapper $form, PageBlockInterface $block): FormBuilderInterface
    {
        $page = $block->getPage();

        $formBuilder = $form->getFormBuilder()->create('pageId', PageSelectorType::class, [
            'model_manager' => $this->pageAdmin->getModelManager(),
            'class' => $this->pageAdmin->getClass(),
            'site' => null !== $page ? $page->getSite() : null,
            'required' => false,
            'label' => 'form.label_page',
            'translation_domain' => 'SonataPageBundle',
        ]);
        $formBuilder->addModelTransformer(new CallbackTransformer(
            static fn (?PageInterface $value): ?PageInterface => $value,
            static fn (?PageInterface $value) => $value instanceof PageInterface ? $value->getId() : $value
        ));

        return $formBuilder;
    }
}
