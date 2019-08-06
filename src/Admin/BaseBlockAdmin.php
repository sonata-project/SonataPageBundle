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
use Sonata\Cache\CacheManagerInterface;
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
     * @var CacheManagerInterface
     */
    protected $cacheManager;

    /**
     * @var bool
     */
    protected $inValidate = false;

    /**
     * @var array
     */
    protected $containerBlockTypes = [];

    public function getObject($id)
    {
        $subject = parent::getObject($id);

        if ($subject) {
            return $this->loadBlockDefaults($subject);
        }

        return $subject;
    }

    public function getNewInstance()
    {
        $block = parent::getNewInstance();
        $block->setType($this->getPersistentParameter('type'));

        return $this->loadBlockDefaults($block);
    }

    /**
     * @param BaseBlock $object
     */
    public function preUpdate($object)
    {
        $block = $this->blockManager->get($object);

        if (\is_callable([$block, 'preUpdate'])) {
            $block->preUpdate($object);

            @trigger_error(
                'The '.__METHOD__.' method is deprecated since version 3.x and will be removed in sonata/block-bundle 4.0.',
                E_USER_DEPRECATED
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
    public function postUpdate($object)
    {
        $block = $this->blockManager->get($object);

        if (\is_callable([$block, 'postUpdate'])) {
            $block->postUpdate($object);

            @trigger_error(
                'The '.__METHOD__.' method is deprecated since version 3.x and will be removed in sonata/block-bundle 4.0.',
                E_USER_DEPRECATED
            );
        }

        $service = $this->blockManager->get($object);

        $this->cacheManager->invalidate($service->getCacheKeys($object));
    }

    /**
     * @param BaseBlock $object
     */
    public function prePersist($object)
    {
        $block = $this->blockManager->get($object);

        if (\is_callable([$block, 'prePersist'])) {
            $block->prePersist($object);

            @trigger_error(
                'The '.__METHOD__.' method is deprecated since version 3.x and will be removed in sonata/block-bundle 4.0.',
                E_USER_DEPRECATED
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
    public function postPersist($object)
    {
        $block = $this->blockManager->get($object);

        if (\is_callable([$block, 'postPersist'])) {
            $block->postPersist($object);

            @trigger_error(
                'The '.__METHOD__.' method is deprecated since version 3.x and will be removed in sonata/block-bundle 4.0.',
                E_USER_DEPRECATED
            );
        }

        $service = $this->blockManager->get($object);

        $this->cacheManager->invalidate($service->getCacheKeys($object));
    }

    /**
     * @param BaseBlock $object
     */
    public function preRemove($object)
    {
        $block = $this->blockManager->get($object);

        if (\is_callable([$block, 'preRemove'])) {
            $block->preRemove($object);

            @trigger_error(
                'The '.__METHOD__.' method is deprecated since version 3.x and will be removed in sonata/block-bundle 4.0.',
                E_USER_DEPRECATED
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
    public function postRemove($object)
    {
        $block = $this->blockManager->get($object);

        if (\is_callable([$block, 'postRemove'])) {
            $block->postRemove($object);

            @trigger_error(
                'The '.__METHOD__.' method is deprecated since version 3.x and will be removed in sonata/block-bundle 4.0.',
                E_USER_DEPRECATED
            );
        }
    }

    public function setBlockManager(BlockServiceManagerInterface $blockManager)
    {
        $this->blockManager = $blockManager;
    }

    public function setCacheManager(CacheManagerInterface $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function setContainerBlockTypes(array $containerBlockTypes)
    {
        $this->containerBlockTypes = $containerBlockTypes;
    }

    public function getPersistentParameters()
    {
        if (!$this->hasRequest()) {
            return [];
        }

        return [
            'type' => $this->getRequest()->get('type'),
        ];
    }

    public function preBatchAction($actionName, ProxyQueryInterface $query, array &$idx, $allElements)
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

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('view', $this->getRouterIdParameter().'/view');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('type')
            ->add('name')
            ->add('enabled', null, ['editable' => true])
            ->add('updatedAt')
            ->add('position')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('enabled')
            ->add('type')
        ;
    }

    /**
     * @return BlockInterface
     */
    private function loadBlockDefaults(BlockInterface $block)
    {
        $service = $this->blockManager->get($block);

        $resolver = new OptionsResolver();
        // use new interface method whenever possible
        // NEXT_MAJOR: Remove this check and legacy setDefaultSettings method call
        if (method_exists($service, 'configureSettings')) {
            $service->configureSettings($resolver);
        } else {
            $service->setDefaultSettings($resolver);
        }

        try {
            $block->setSettings($resolver->resolve($block->getSettings()));
        } catch (InvalidOptionsException $e) {
            // @TODO : add a logging error or a flash message
        }

        $service->load($block);

        return $block;
    }
}
