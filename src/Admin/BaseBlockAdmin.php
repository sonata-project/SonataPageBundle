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

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\Entity\BaseBlock;
use Sonata\PageBundle\Model\PageInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Abstract admin class for the Block model.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseBlockAdmin extends AbstractAdmin
{
    /**
     * @var BlockServiceManagerInterface
     */
    protected $blockManager;

    /**
     * @var bool
     */
    protected $inValidate = false;

    /**
     * @var array
     */
    protected $containerBlockTypes = [];

    /**
     * @param BaseBlock $object
     */
    public function preUpdate($object): void
    {
        $block = $this->blockManager->get($object);

        if (\is_callable([$block, 'preUpdate'])) {
            $block->preUpdate($object);

            @trigger_error(
                'The '.__METHOD__.'() method is deprecated since sonata-project/block-bundle 3.12.0 and will be removed in version 4.0.',
                \E_USER_DEPRECATED
            );
        }

        // fix weird bug with setter object not being call
        $object->setChildren($object->getChildren());

        if ($object->getPage() instanceof PageInterface) {
            $object->getPage()->setEdited(true);
        }
    }

    /**
     * @param BaseBlock $object
     */
    public function postUpdate($object): void
    {
        $block = $this->blockManager->get($object);

        if (\is_callable([$block, 'postUpdate'])) {
            $block->postUpdate($object);

            @trigger_error(
                'The '.__METHOD__.'() method is deprecated since sonata-project/block-bundle 3.12.0 and will be removed in version 4.0.',
                \E_USER_DEPRECATED
            );
        }
    }

    /**
     * @param BaseBlock $object
     */
    public function prePersist($object): void
    {
        $block = $this->blockManager->get($object);

        if (\is_callable([$block, 'prePersist'])) {
            $block->prePersist($object);

            @trigger_error(
                'The '.__METHOD__.'() method is deprecated since sonata-project/block-bundle 3.12.0 and will be removed in version 4.0.',
                \E_USER_DEPRECATED
            );
        }

        if ($object->getPage() instanceof PageInterface) {
            $object->getPage()->setEdited(true);
        }

        // fix weird bug with setter object not being call
        $object->setChildren($object->getChildren());
    }

    /**
     * @param BaseBlock $object
     */
    public function postPersist($object): void
    {
        $block = $this->blockManager->get($object);

        if (\is_callable([$block, 'postPersist'])) {
            $block->postPersist($object);

            @trigger_error(
                'The '.__METHOD__.'() method is deprecated since sonata-project/block-bundle 3.12.0 and will be removed in version 4.0.',
                \E_USER_DEPRECATED
            );
        }
    }

    /**
     * @param BaseBlock $object
     */
    public function preRemove($object): void
    {
        $block = $this->blockManager->get($object);

        if (\is_callable([$block, 'preRemove'])) {
            $block->preRemove($object);

            @trigger_error(
                'The '.__METHOD__.'() method is deprecated since sonata-project/block-bundle 3.12.0 and will be removed in version 4.0.',
                \E_USER_DEPRECATED
            );
        }

        $page = $object->getPage();

        if ($page instanceof PageInterface) {
            $page->setEdited(true);
        }
    }

    /**
     * @param BaseBlock $object
     */
    public function postRemove($object): void
    {
        $block = $this->blockManager->get($object);

        if (\is_callable([$block, 'postRemove'])) {
            $block->postRemove($object);

            @trigger_error(
                'The '.__METHOD__.'() method is deprecated since sonata-project/block-bundle 3.12.0 and will be removed in version 4.0.',
                \E_USER_DEPRECATED
            );
        }
    }

    public function setBlockManager(BlockServiceManagerInterface $blockManager): void
    {
        $this->blockManager = $blockManager;
    }

    public function setContainerBlockTypes(array $containerBlockTypes): void
    {
        $this->containerBlockTypes = $containerBlockTypes;
    }

    public function getPersistentParameters(): array
    {
        if (!$this->hasRequest()) {
            return [];
        }

        return [
            'type' => $this->getRequest()->get('type'),
        ];
    }

    public function preBatchAction($actionName, ProxyQueryInterface $query, array &$idx, $allElements): void
    {
        $parent = $this->getParent();

        if ($parent && 'delete' === $actionName) {
            $subject = $parent->getSubject();

            if ($subject instanceof PageInterface) {
                $subject->setEdited(true);
            }
        }

        parent::preBatchAction($actionName, $query, $idx, $allElements);
    }

    protected function alterObject(object $object): void
    {
        $this->loadBlockDefaults($object);
    }

    protected function alterNewInstance(object $object): void
    {
        $object->setType($this->getPersistentParameter('type'));

        $this->loadBlockDefaults($object);
    }

    protected function configurePersistentParameters(): array
    {
        if (!$this->hasRequest()) {
            return [];
        }

        return [
            'type' => $this->getRequest()->get('type'),
        ];
    }

    protected function configureRoutes(RouteCollection $collection): void
    {
        $collection->add('view', $this->getRouterIdParameter().'/view');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('type')
            ->add('name')
            ->add('enabled', null, ['editable' => true])
            ->add('updatedAt')
            ->add('position');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('enabled')
            ->add('type');
    }

    private function loadBlockDefaults(BlockInterface $block): BlockInterface
    {
        $blockType = $block->getType();

        if (null === $blockType || !$this->blockManager->has($blockType)) {
            return $block;
        }

        $service = $this->blockManager->get($block);

        $resolver = new OptionsResolver();
        $service->configureSettings($resolver);

        try {
            $block->setSettings($resolver->resolve($block->getSettings()));
        } catch (InvalidOptionsException $e) {
            // @TODO : add a logging error or a flash message
        }

        $service->load($block);

        return $block;
    }
}
