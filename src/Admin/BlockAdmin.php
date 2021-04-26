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
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\BlockBundle\Block\BlockServiceInterface;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Type\ServiceListType;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\Mapper\PageFormMapper;
use Sonata\PageBundle\Model\PageInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Admin class for the Block model.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BlockAdmin extends BaseBlockAdmin
{
    /**
     * @var array
     */
    protected $blocks;

    protected $classnameLabel = 'Block';

    protected $accessMapping = [
        'savePosition' => 'EDIT',
        'switchParent' => 'EDIT',
        'composePreview' => 'EDIT',
    ];

    /**
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     */
    public function __construct($code, $class, $baseControllerName, array $blocks = [])
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->blocks = $blocks;
    }

    protected function configurePersistentParameters(): array
    {
        $parameters = parent::configurePersistentParameters();

        if (!$this->hasRequest()) {
            return $parameters;
        }

        if ($composer = $this->getRequest()->get('composer')) {
            $parameters['composer'] = $composer;
        }

        return $parameters;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);

        $collection->add('savePosition', 'save-position');
        $collection->add('switchParent', 'switch-parent');
        $collection->add('composePreview', '{block_id}/compose_preview', [
            'block_id' => null,
        ]);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $block = $this->getSubject();

        if (!$block) { // require to have a proper running test suite at the sandbox level
            return;
        }

        $page = false;

        if ($this->getParent()) {
            $page = $this->getParent()->getSubject();

            if (!$page instanceof PageInterface) {
                throw new \RuntimeException('The BlockAdmin must be attached to a parent PageAdmin');
            }

            if ($this->hasRequest() && null === $block->getId()) { // new block
                $block->setType($this->request->get('type'));
                $block->setPage($page);
            }

            if ($block->getPage()->getId() !== $page->getId()) {
                throw new \RuntimeException('The page reference on BlockAdmin and parent admin are not the same');
            }
        }

        $blockType = $block->getType();

        $isComposer = $this->hasRequest() ? $this->getRequest()->get('composer', false) : false;
        $generalGroupOptions = $optionsGroupOptions = [];
        if ($isComposer) {
            $generalGroupOptions['class'] = 'hidden';
            $optionsGroupOptions['name'] = '';
        }

        $formMapper->with('form.field_group_general', $generalGroupOptions);

        if (!$isComposer) {
            $formMapper->add('name');
        } else {
            $formMapper->add('name', HiddenType::class);
        }

        $formMapper->end();

        $isContainerRoot = $block && \in_array($blockType, ['sonata.page.block.container', 'sonata.block.service.container'], true) && !$this->hasParentFieldDescription();
        $isStandardBlock = $block && !\in_array($blockType, ['sonata.page.block.container', 'sonata.block.service.container'], true) && !$this->hasParentFieldDescription();

        if ($isContainerRoot || $isStandardBlock) {
            $formMapper->with('form.field_group_general', $generalGroupOptions);

            $containerBlockTypes = $this->containerBlockTypes;

            // need to investigate on this case where $page == null ... this should not be possible
            if ($isStandardBlock && $page && !empty($containerBlockTypes)) {
                $formMapper->add('parent', EntityType::class, [
                    'class' => $this->getClass(),
                    'query_builder' => static function (EntityRepository $repository) use ($page, $containerBlockTypes) {
                        return $repository->createQueryBuilder('a')
                            ->andWhere('a.page = :page AND a.type IN (:types)')
                            ->setParameters([
                                    'page' => $page,
                                    'types' => $containerBlockTypes,
                                ]);
                    },
                ], [
                    'admin_code' => $this->getCode(),
                ]);
            }

            if ($isComposer) {
                $formMapper->add('enabled', HiddenType::class, ['data' => true]);
            } else {
                $formMapper->add('enabled');
            }

            if ($isStandardBlock) {
                $formMapper->add('position', IntegerType::class);
            }

            $formMapper->end();

            $formMapper->with('form.field_group_options', $optionsGroupOptions);

            $this->configureBlockFields($formMapper, $block);

            $formMapper->end();
        } else {
            $formMapper
                ->with('form.field_group_options', $optionsGroupOptions)
                ->add('type', ServiceListType::class, ['context' => 'sonata_page_bundle'])
                ->add('enabled')
                ->add('position', IntegerType::class)
                ->end();
        }
    }

    /**
     * @return string|null
     */
    private function getDefaultTemplate(BlockServiceInterface $blockService)
    {
        $resolver = new OptionsResolver();
        // use new interface method whenever possible
        // NEXT_MAJOR: Remove this check and legacy setDefaultSettings method call
        if (method_exists($blockService, 'configureSettings')) {
            $blockService->configureSettings($resolver);
        } else {
            $blockService->setDefaultSettings($resolver);
        }
        $options = $resolver->resolve();

        if (isset($options['template'])) {
            return $options['template'];
        }
    }

    private function configureBlockFields(FormMapper $formMapper, BlockInterface $block): void
    {
        $blockType = $block->getType();

        if (null === $blockType || !$this->blockManager->has($blockType)) {
            return;
        }

        $service = $this->blockManager->get($block);

        if (!$service instanceof BlockServiceInterface) {
            throw new \RuntimeException(sprintf(
                'The block "%s" must implement %s',
                $blockType,
                BlockServiceInterface::class
            ));
        }

        if ($service instanceof EditableBlockService) {
            $blockMapper = new PageFormMapper($formMapper);
            if ($block->getId() > 0) {
                $service->configureEditForm($blockMapper, $block);
            } else {
                $service->configureCreateForm($blockMapper, $block);
            }
        } else {
            @trigger_error(
                sprintf(
                    'Editing a block service which doesn\'t implement %s is deprecated since sonata-project/page-bundle 3.12.0 and will not be allowed with version 4.0.',
                    EditableBlockService::class
                ),
                \E_USER_DEPRECATED
            );

            if ($block->getId() > 0) {
                $service->buildEditForm($formMapper, $block);
            } else {
                $service->buildCreateForm($formMapper, $block);
            }
        }

        if ($formMapper->has('settings') && isset($this->blocks[$blockType]['templates'])) {
            $settingsField = $formMapper->get('settings');

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
