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

    /**
     * {@inheritdoc}
     */
    public function getObject($id)
    {
        $subject = parent::getObject($id);

        if ($subject) {
            return $this->loadBlockDefaults($subject);
        }

        return $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
        $block = parent::getNewInstance();
        $block->setType($this->getPersistentParameter('type'));

        return $this->loadBlockDefaults($block);
    }

    /**
     * {@inheritdoc}
     *
     * @param BaseBlock $object
     */
    public function preUpdate($object)
    {
        $this->blockManager->get($object)->preUpdate($object);

        // fix weird bug with setter object not being call
        $object->setChildren($object->getChildren());

        if ($object->getPage() instanceof PageInterface) {
            $object->getPage()->setEdited(true);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param BaseBlock $object
     */
    public function postUpdate($object)
    {
        $this->blockManager->get($object)->postUpdate($object);

        $service = $this->blockManager->get($object);

        $this->cacheManager->invalidate($service->getCacheKeys($object));
    }

    /**
     * {@inheritdoc}
     *
     * @param BaseBlock $object
     */
    public function prePersist($object)
    {
        $this->blockManager->get($object)->prePersist($object);

        if ($object->getPage() instanceof PageInterface) {
            $object->getPage()->setEdited(true);
        }

        // fix weird bug with setter object not being call
        $object->setChildren($object->getChildren());
    }

    /**
     * {@inheritdoc}
     *
     * @param BaseBlock $object
     */
    public function postPersist($object)
    {
        $this->blockManager->get($object)->postPersist($object);

        $service = $this->blockManager->get($object);

        $this->cacheManager->invalidate($service->getCacheKeys($object));
    }

    /**
     * {@inheritdoc}
     *
     * @param BaseBlock $object
     */
    public function preRemove($object)
    {
        $this->blockManager->get($object)->preRemove($object);

        $page = $object->getPage();

        if ($page instanceof PageInterface) {
            $page->setEdited(true);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param BaseBlock $object
     */
    public function postRemove($object)
    {
        $this->blockManager->get($object)->postRemove($object);
    }

    /**
     * @param BlockServiceManagerInterface $blockManager
     */
    public function setBlockManager(BlockServiceManagerInterface $blockManager)
    {
        $this->blockManager = $blockManager;
    }

    /**
     * @param CacheManagerInterface $cacheManager
     */
    public function setCacheManager(CacheManagerInterface $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param array $containerBlockTypes
     */
    public function setContainerBlockTypes(array $containerBlockTypes)
    {
        $this->containerBlockTypes = $containerBlockTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentParameters()
    {
        if (!$this->hasRequest()) {
            return [];
        }

        return [
            'type' => $this->getRequest()->get('type'),
        ];
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('view', $this->getRouterIdParameter().'/view');
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('enabled')
            ->add('type')
        ;
    }

    /**
     * @param BlockInterface $block
     *
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
