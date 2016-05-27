<?php

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
use Sonata\PageBundle\Model\PageInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Admin class for the Block model.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BlockAdmin extends BaseBlockAdmin
{
    /**
     * {@inheritdoc}
     */
    protected $parentAssociationMapping = 'page';

    /**
     * @var array
     */
    protected $blocks;

    /**
     * BlockAdmin constructor.
     *
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     * @param array  $blocks
     */
    public function __construct($code, $class, $baseControllerName, array $blocks = [])
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->blocks = $blocks;
    }

    /**
     * {@inheritdoc}
     */
    protected $accessMapping = [
        'savePosition'   => 'EDIT',
        'switchParent'   => 'EDIT',
        'composePreview' => 'EDIT',
    ];

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);

        $collection->add('savePosition', 'save-position');
        $collection->add('switchParent', 'switch-parent');
        $collection->add('composePreview', '{block_id}/compose_preview', [
            'block_id' => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $block = $this->getSubject();

        $page = false;

        if ($this->getParent()) {
            $page = $this->getParent()->getSubject();

            if (!$page instanceof PageInterface) {
                throw new \RuntimeException('The BlockAdmin must be attached to a parent PageAdmin');
            }

            if ($block->getId() === null) { // new block
                $block->setType($this->request->get('type'));
                $block->setPage($page);
            }

            if ($block->getPage()->getId() != $page->getId()) {
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
            $formMapper->add('name', 'hidden');
        }

        $formMapper->end();

        $isContainerRoot = $block && in_array($blockType, ['sonata.page.block.container', 'sonata.block.service.container']) && !$this->hasParentFieldDescription();
        $isStandardBlock = $block && !in_array($blockType, ['sonata.page.block.container', 'sonata.block.service.container']) && !$this->hasParentFieldDescription();

        if ($isContainerRoot || $isStandardBlock) {
            $formMapper->with('form.field_group_general', $generalGroupOptions);

            $service = $this->blockManager->get($block);

            $containerBlockTypes = $this->containerBlockTypes;

            // need to investigate on this case where $page == null ... this should not be possible
            if ($isStandardBlock && $page && !empty($containerBlockTypes)) {
                $formMapper->add('parent', 'entity', [
                        'class'         => $this->getClass(),
                        'query_builder' => function (EntityRepository $repository) use ($page, $containerBlockTypes) {
                            return $repository->createQueryBuilder('a')
                                ->andWhere('a.page = :page AND a.type IN (:types)')
                                ->setParameters([
                                        'page'  => $page,
                                        'types' => $containerBlockTypes,
                                    ]);
                        },
                    ], [
                        'admin_code' => $this->getCode(),
                    ]);
            }

            if ($isComposer) {
                $formMapper->add('enabled', 'hidden', ['data' => true]);
            } else {
                $formMapper->add('enabled');
            }

            if ($isStandardBlock) {
                $formMapper->add('position', 'integer');
            }

            $formMapper->end();

            $formMapper->with('form.field_group_options', $optionsGroupOptions);

            if ($block->getId() > 0) {
                $service->buildEditForm($formMapper, $block);
            } else {
                $service->buildCreateForm($formMapper, $block);
            }

            if ($formMapper->has('settings') && isset($this->blocks[$blockType]['templates'])) {
                $settingsField = $formMapper->get('settings');

                if (!$settingsField->has('template')) {
                    $choices = [];

                    if (null !== $defaultTemplate = $this->getDefaultTemplate($service)) {
                        $choices[$defaultTemplate] = 'default';
                    }

                    foreach ($this->blocks[$blockType]['templates'] as $item) {
                        $choices[$item['template']] = $item['name'];
                    }

                    if (count($choices) > 1) {
                        $settingsField->add('template', 'choice', ['choices' => $choices]);
                    }
                }
            }

            $formMapper->end();
        } else {
            $formMapper
                ->with('form.field_group_options', $optionsGroupOptions)
                ->add('type', 'sonata_block_service_choice', [
                        'context' => 'sonata_page_bundle',
                    ])
                ->add('enabled')
                ->add('position', 'integer')
                ->end();
        }
    }

    /**
     * @param BlockServiceInterface $blockService
     *
     * @return string|null
     */
    private function getDefaultTemplate(BlockServiceInterface $blockService)
    {
        $resolver = new OptionsResolver();
        $blockService->setDefaultSettings($resolver);
        $options = $resolver->resolve();

        if (isset($options['template'])) {
            return $options['template'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentParameters()
    {
        $parameters = parent::getPersistentParameters();

        if ($composer = $this->getRequest()->get('composer')) {
            $parameters['composer'] = $composer;
        }

        return $parameters;
    }
}
