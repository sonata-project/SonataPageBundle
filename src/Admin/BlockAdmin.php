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

namespace Sonata\PageBundle\Admin;

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Block\Service\BlockServiceInterface;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Type\ServiceListType;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class BlockAdmin extends BaseBlockAdmin
{
    protected $classnameLabel = 'Block';

    /**
     * @param array<string, array{
     *   templates?: array<array{
     *     name: string,
     *     template: string,
     *   }>,
     * }> $blocks
     */
    public function __construct(
        BlockServiceManagerInterface $blockManager,
        private array $blocks = [],
    ) {
        parent::__construct($blockManager);
    }

    protected function getAccessMapping(): array
    {
        return [
            'savePosition' => AdminPermissionMap::PERMISSION_EDIT,
            'switchParent' => AdminPermissionMap::PERMISSION_EDIT,
            'composePreview' => AdminPermissionMap::PERMISSION_EDIT,
        ];
    }

    protected function configurePersistentParameters(): array
    {
        $parameters = parent::configurePersistentParameters();

        if (!$this->hasRequest()) {
            return $parameters;
        }

        $composer = $this->getRequest()->get('composer');

        if (null !== $composer) {
            $parameters['composer'] = $composer;
        }

        return $parameters;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        parent::configureRoutes($collection);

        $collection->add('save_position', 'save-position');
        $collection->add('switch_parent', 'switch-parent');
        $collection->add('compose_preview', $this->getRouterIdParameter().'/compose-preview');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $block = $this->hasSubject() ? $this->getSubject() : null;

        if (null === $block) { // require to have a proper running test suite at the sandbox level
            return;
        }

        $page = null;

        if ($this->isChild()) {
            $page = $this->getParent()->getSubject();

            if (!$page instanceof PageInterface) {
                throw new \RuntimeException('The BlockAdmin must be attached to a parent PageAdmin');
            }

            if ($this->hasRequest() && null === $block->getId()) { // new block
                $block->setType($this->getRequest()->get('type'));
                $block->setPage($page);
            }

            $blockPage = $block->getPage();

            if (null === $blockPage || $blockPage->getId() !== $page->getId()) {
                throw new \RuntimeException('The page reference on BlockAdmin and parent admin are not the same');
            }
        }

        $blockType = $block->getType();

        $isComposer = $this->hasRequest() ? $this->getRequest()->get('composer', false) : false;
        $generalGroupOptions = $optionsGroupOptions = [];
        if (false !== $isComposer) {
            $generalGroupOptions['class'] = 'hidden';
            $optionsGroupOptions['name'] = '';
        }

        $form->with('general', $generalGroupOptions);

        if (false !== $isComposer) {
            $form->add('name', HiddenType::class);
        } else {
            $form->add('name');
        }

        $form->end();

        $isContainerRoot = \in_array($blockType, ['sonata.page.block.container', 'sonata.block.service.container'], true) && !$this->hasParentFieldDescription();
        $isStandardBlock = !\in_array($blockType, ['sonata.page.block.container', 'sonata.block.service.container'], true) && !$this->hasParentFieldDescription();

        if ($isContainerRoot || $isStandardBlock) {
            $form->with('general', $generalGroupOptions);

            $containerBlockTypes = $this->containerBlockTypes;

            // need to investigate on this case where $page == null ... this should not be possible
            if ($isStandardBlock && null !== $page && [] !== $containerBlockTypes) {
                $form->add('parent', EntityType::class, [
                    'class' => $this->getClass(),
                    'query_builder' => static fn (EntityRepository $repository) => $repository->createQueryBuilder('a')
                        ->andWhere('a.page = :page AND a.type IN (:types)')
                        ->setParameter('page', $page->getId())
                        ->setParameter('types', $containerBlockTypes),
                ], [
                    'admin_code' => $this->getCode(),
                ]);
            }

            if ($isStandardBlock) {
                $form->add('position', IntegerType::class);
            }

            if (false !== $isComposer) {
                $form->add('enabled', HiddenType::class, ['data' => true]);
            } else {
                $form->add('enabled');
            }

            $form->end();

            $form->with('options', $optionsGroupOptions);

            $this->configureBlockFields($form, $block);

            $form->end();
        } else {
            $form
                ->with('options', $optionsGroupOptions)
                    ->add('type', ServiceListType::class, ['context' => 'sonata_page_bundle'])
                    ->add('enabled')
                    ->add('position', IntegerType::class)
                ->end();
        }
    }

    private function getDefaultTemplate(BlockServiceInterface $blockService): ?string
    {
        $resolver = new OptionsResolver();
        $blockService->configureSettings($resolver);
        $options = $resolver->resolve();

        return $options['template'] ?? null;
    }

    /**
     * @param FormMapper<PageBlockInterface> $form
     */
    private function configureBlockFields(FormMapper $form, BlockInterface $block): void
    {
        $blockType = $block->getType();

        if (null === $blockType || !$this->blockManager->has($blockType)) {
            return;
        }

        $service = $this->blockManager->get($block);

        if (!$service instanceof EditableBlockService) {
            throw new \RuntimeException(\sprintf(
                'The block "%s" must implement %s',
                $blockType,
                EditableBlockService::class
            ));
        }

        if ($block->getId() > 0) {
            $service->configureEditForm($form, $block);
        } else {
            $service->configureCreateForm($form, $block);
        }

        if ($form->has('settings') && isset($this->blocks[$blockType]['templates'])) {
            $settingsField = $form->get('settings');

            if (!$settingsField->has('template')) {
                $choices = [];

                if (null !== $defaultTemplate = $this->getDefaultTemplate($service)) {
                    $choices['default'] = $defaultTemplate;
                }

                foreach ($this->blocks[$blockType]['templates'] as $item) {
                    $choices[$item['name']] = $item['template'];
                }

                if (\count($choices) > 1) {
                    $templateOptions = [
                        'choices' => $choices,
                    ];

                    $settingsField->add('template', ChoiceType::class, $templateOptions);
                }
            }
        }
    }
}
