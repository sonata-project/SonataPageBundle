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
use Sonata\BlockBundle\Model\BlockManagerInterface;
use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\PageBundle\Admin\SharedBlockAdmin;
use Sonata\PageBundle\Model\Block;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Render a shared block.
 *
 * @author Romain Mouillard <romain.mouillard@gmail.com>
 */
class SharedBlockBlockService extends AbstractAdminBlockService
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
     * @var BlockManagerInterface
     */
    private $blockManager;

    /**
     * @param string                $name
     * @param EngineInterface       $templating
     * @param ContainerInterface    $container
     * @param BlockManagerInterface $blockManager
     */
    public function __construct($name, EngineInterface $templating, ContainerInterface $container, BlockManagerInterface $blockManager)
    {
        $this->name = $name;
        $this->templating = $templating;
        $this->container = $container;
        $this->blockManager = $blockManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
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

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        $errorElement
            ->with('settings[blockId]')
                ->addConstraint(new NotBlank())
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        if (!$block->getSetting('blockId') instanceof BlockInterface) {
            $this->load($block);
        }

        $formMapper->add('settings', 'sonata_type_immutable_array', [
            'keys' => [
                [$this->getBlockBuilder($formMapper), null, []],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Shared Block';
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'template' => 'SonataPageBundle:Block:block_shared_block.html.twig',
            'blockId' => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function load(BlockInterface $block)
    {
        $sharedBlock = $block->getSetting('blockId', null);

        if (is_int($sharedBlock)) {
            $sharedBlock = $this->blockManager->findOneBy(['id' => $sharedBlock]);
        }

        $block->setSetting('blockId', $sharedBlock);
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(BlockInterface $block)
    {
        $block->setSetting('blockId', is_object($block->getSetting('blockId')) ? $block->getSetting('blockId')->getId() : null);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(BlockInterface $block)
    {
        $block->setSetting('blockId', is_object($block->getSetting('blockId')) ? $block->getSetting('blockId')->getId() : null);
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
     * @param FormMapper $formMapper
     *
     * @return FormBuilder
     */
    protected function getBlockBuilder(FormMapper $formMapper)
    {
        // simulate an association ...
        $fieldDescription = $this->getSharedBlockAdmin()->getModelManager()->getNewFieldDescriptionInstance($this->sharedBlockAdmin->getClass(), 'block', [
            'translation_domain' => 'SonataPageBundle',
        ]);
        $fieldDescription->setAssociationAdmin($this->getSharedBlockAdmin());
        $fieldDescription->setAdmin($formMapper->getAdmin());
        $fieldDescription->setOption('edit', 'list');
        $fieldDescription->setAssociationMapping([
                'fieldName' => 'block',
                'type' => \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE,
            ]);

        return $formMapper->create('blockId', 'sonata_type_model_list', [
                'sonata_field_description' => $fieldDescription,
                'class' => $this->getSharedBlockAdmin()->getClass(),
                'model_manager' => $this->getSharedBlockAdmin()->getModelManager(),
                'label' => 'form.label_block',
                'required' => false,
            ]);
    }
}
