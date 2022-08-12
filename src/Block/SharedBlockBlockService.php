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

use Sonata\AdminBundle\Form\FormMapper as AdminFormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Sonata\PageBundle\Model\PageBlockInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Twig\Environment;

/**
 * @author Romain Mouillard <romain.mouillard@gmail.com>
 */
final class SharedBlockBlockService extends AbstractBlockService implements EditableBlockService
{
    /**
     * @var ManagerInterface<PageBlockInterface>
     */
    private ManagerInterface $blockManager;

    /**
     * @param ManagerInterface<PageBlockInterface> $blockManager
     */
    public function __construct(
        Environment $twig,
        ManagerInterface $blockManager
    ) {
        parent::__construct($twig);

        $this->blockManager = $blockManager;
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $block = $blockContext->getBlock();

        if (!$block->getSetting('blockId') instanceof BlockInterface) {
            $this->load($block);
        }

        $sharedBlock = $block->getSetting('blockId');

        $template = $blockContext->getTemplate();
        \assert(null !== $template);

        return $this->renderResponse($template, [
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

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        if (!$form instanceof AdminFormMapper) {
            throw new \InvalidArgumentException('Shared Block requires to be used in the Admin context');
        }

        if (!$block->getSetting('blockId') instanceof BlockInterface) {
            $this->load($block);
        }

        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [
                [$this->getBlockBuilder($form), null, []],
            ],
        ]);
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => '@SonataPage/Block/block_shared_block.html.twig',
            'blockId' => null,
        ]);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('Shared Block', null, null, 'SonataPageBundle', [
            'class' => 'fa fa-home',
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

    /**
     * @param AdminFormMapper<object> $form
     */
    private function getBlockBuilder(AdminFormMapper $form): FormBuilderInterface
    {
        $admin = $form->getAdmin();

        $fieldDescription = $admin->createFieldDescription('block', [
            'translation_domain' => 'SonataPageBundle',
            'edit' => 'list',
        ]);
        $fieldDescription->setAssociationAdmin($admin);

        return $form->getFormBuilder()->create('blockId', ModelListType::class, [
            'sonata_field_description' => $fieldDescription,
            'class' => $admin->getClass(),
            'model_manager' => $admin->getModelManager(),
            'label' => 'form.label_block',
            'required' => false,
        ]);
    }
}
