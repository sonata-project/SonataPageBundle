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
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Sonata\PageBundle\Model\Block;
use Sonata\PageBundle\Model\PageBlockInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Twig\Environment;

/**
 * @author Romain Mouillard <romain.mouillard@gmail.com>
 */
final class SharedBlockBlockService extends AbstractBlockService
{
    private ManagerInterface $blockManager;

    /**
     * @var AdminInterface<PageBlockInterface>
     */
    private AdminInterface $sharedBlockAdmin;

    public function __construct(
        Environment $twig,
        ManagerInterface $blockManager,
        AdminInterface $sharedBlockAdmin
    ) {
        parent::__construct($twig);

        $this->blockManager = $blockManager;
        $this->sharedBlockAdmin = $sharedBlockAdmin;
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

    public function validateBlock(ErrorElement $errorElement, BlockInterface $block): void
    {
        $errorElement
            ->with('settings[blockId]')
                ->addConstraint(new NotBlank())
            ->end();
    }

    public function buildEditForm(FormMapper $form, BlockInterface $block): void
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

    public function getName()
    {
        return 'Shared Block';
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

    protected function getBlockBuilder(FormMapper $form): FormBuilderInterface
    {
        $fieldDescription = $this->sharedBlockAdmin->createFieldDescription('block', [
            'translation_domain' => 'SonataPageBundle',
            'edit' => 'list',
        ]);

        return $form->create('blockId', ModelListType::class, [
            'sonata_field_description' => $fieldDescription,
            'class' => $this->sharedBlockAdmin->getClass(),
            'model_manager' => $this->sharedBlockAdmin->getModelManager(),
            'label' => 'form.label_block',
            'required' => false,
        ]);
    }
}
