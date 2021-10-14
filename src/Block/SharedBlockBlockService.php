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

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Sonata\PageBundle\Admin\SharedBlockAdmin;
use Sonata\PageBundle\Model\Block;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Twig\Environment;

/**
 * Render a shared block.
 *
 * @author Romain Mouillard <romain.mouillard@gmail.com>
 */
class SharedBlockBlockService extends AbstractBlockService implements EditableBlockService
{
    /**
     * @var SharedBlockAdmin
     */
    private $sharedBlockAdmin;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ManagerInterface
     */
    private $blockManager;

    public function __construct(Environment $twig, ContainerInterface $container, BlockManagerInterface $blockManager)
    {
        parent::__construct($twig);

        $this->container = $container;
        $this->blockManager = $blockManager;
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $block = $blockContext->getBlock();

        if (!$block->getSetting('blockId') instanceof BlockInterface) {
            $this->load($block);
        }

        /** @var Block $sharedBlock */
        $sharedBlock = $block->getSetting('blockId');

        return $this->renderResponse($blockContext->getTemplate(), [
                'block' => $blockContext->getBlock(),
                'settings' => $blockContext->getSettings(),
                'sharedBlock' => $sharedBlock,
            ], $response);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
        $errorElement
            ->with('settings[blockId]')
                ->addConstraint(new NotBlank())
            ->end();
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        if (!$block->getSetting('blockId') instanceof BlockInterface) {
            $this->load($block);
        }

        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [
                [$this->getBlockBuilder($form), null, []],
            ],
        ]);
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => '@SonataPage/Block/block_shared_block.html.twig',
            'blockId' => null,
        ]);
    }

    public function load(BlockInterface $block): void
    {
        $sharedBlock = $block->getSetting('blockId', null);

        if (\is_int($sharedBlock)) {
            $sharedBlock = $this->blockManager->findOneBy(['id' => $sharedBlock]);
        }

        $block->setSetting('blockId', $sharedBlock);
    }

    public function prePersist(BlockInterface $block): void
    {
        $block->setSetting('blockId', \is_object($block->getSetting('blockId')) ? $block->getSetting('blockId')->getId() : null);
    }

    public function preUpdate(BlockInterface $block): void
    {
        $block->setSetting('blockId', \is_object($block->getSetting('blockId')) ? $block->getSetting('blockId')->getId() : null);
    }

    /**
     * @return SharedBlockAdmin
     */
    protected function getSharedBlockAdmin()
    {
        if (!$this->sharedBlockAdmin) {
            $this->sharedBlockAdmin = $this->container->get('sonata.page.admin.shared_block');
        }

        return $this->sharedBlockAdmin;
    }

    /**
     * @return FormBuilder
     */
    protected function getBlockBuilder(FormMapper $form)
    {
        // simulate an association ...
        $fieldDescription = $this->getSharedBlockAdmin()->getModelManager()->getNewFieldDescriptionInstance($this->sharedBlockAdmin->getClass(), 'block', [
            'translation_domain' => 'SonataPageBundle',
        ]);
        $fieldDescription->setAssociationAdmin($this->getSharedBlockAdmin());
        $fieldDescription->setAdmin($form->getAdmin());
        $fieldDescription->setOption('edit', 'list');
        $fieldDescription->setAssociationMapping([
            'fieldName' => 'block',
            'type' => ClassMetadataInfo::MANY_TO_ONE,
        ]);

        return $form->create('blockId', ModelListType::class, [
            'sonata_field_description' => $fieldDescription,
            'class' => $this->getSharedBlockAdmin()->getClass(),
            'model_manager' => $this->getSharedBlockAdmin()->getModelManager(),
            'label' => 'form.label_block',
            'required' => false,
        ]);
    }
}
