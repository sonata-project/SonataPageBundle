<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Admin;

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

use Sonata\Cache\CacheManagerInterface;

use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\PageBundle\Model\PageInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Admin class for the Block model
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BlockAdmin extends Admin
{
    protected $parentAssociationMapping = 'page';

    /**
     * @var BlockServiceManagerInterface
     */
    protected $blockManager;

    protected $cacheManager;

    protected $inValidate = false;

    /**
     * @var array
     */
    protected $containerBlockTypes = array();

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('savePosition', 'save-position');
        $collection->add('view', $this->getRouterIdParameter().'/view');
        $collection->add('switchParent', 'switch-parent');

    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('type')
            ->add('name')
            ->add('enabled')
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

        $formMapper->with($this->trans('form.field_group_general'));

        // add name on all forms
        $formMapper->add('name');

        $isContainerRoot = $block && in_array($block->getType(), array('sonata.page.block.container', 'sonata.block.service.container')) && !$this->hasParentFieldDescription();
        $isStandardBlock = $block && !in_array($block->getType(), array('sonata.page.block.container', 'sonata.block.service.container')) && !$this->hasParentFieldDescription();

        if ($isContainerRoot || $isStandardBlock) {
            $service = $this->blockManager->get($block);

            $containerBlockTypes = $this->containerBlockTypes;

            // need to investigate on this case where $page == null ... this should not be possible
            if ($isStandardBlock && $page && !empty($containerBlockTypes)) {
                $formMapper->add('parent', 'entity', array(
                    'class' => $this->getClass(),
                    'query_builder' => function(EntityRepository $repository) use ($page, $containerBlockTypes) {
                        return $repository->createQueryBuilder('a')
                            ->andWhere('a.page = :page AND a.type IN (:types)')
                            ->setParameters(array(
                                'page'  => $page,
                                'types' => $containerBlockTypes,
                            ));
                    }
                ));
            }

            $formMapper->add('enabled');

            if ($isStandardBlock) {
                $formMapper->add('position', 'integer');
            }

            $formMapper->with($this->trans('form.field_group_options'));

            if ($block->getId() > 0) {
                $service->buildEditForm($formMapper, $block);
            } else {
                $service->buildCreateForm($formMapper, $block);
            }

        } else {

            $formMapper
                ->add('type', 'sonata_block_service_choice', array(
                    'context' => 'sonata_page_bundle'
                ))
                ->add('enabled')
                ->add('position', 'integer');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getObject($id)
    {
        $subject = parent::getObject($id);

        if ($subject) {
            $service = $this->blockManager->get($subject);

            $resolver = new OptionsResolver();
            $service->setDefaultSettings($resolver);

            try {
                $subject->setSettings($resolver->resolve($subject->getSettings()));
            } catch (InvalidOptionsException $e) {
                // @TODO : add a logging error or a flash message

            }

            $service->load($subject);
        }

        return $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        $this->blockManager->get($object)->preUpdate($object);

        // fix weird bug with setter object not being call
        $object->setChildren($object->getChildren());
        $object->getPage()->setEdited(true);
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate($object)
    {
        $this->blockManager->get($object)->postUpdate($object);

        $service = $this->blockManager->get($object);

        $this->cacheManager->invalidate($service->getCacheKeys($object));
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        $this->blockManager->get($object)->prePersist($object);

        $object->getPage()->setEdited(true);

        // fix weird bug with setter object not being call
        $object->setChildren($object->getChildren());
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist($object)
    {
        $this->blockManager->get($object)->postPersist($object);

        $service = $this->blockManager->get($object);

        $this->cacheManager->invalidate($service->getCacheKeys($object));
    }

    /**
     * {@inheritdoc}
     */
    public function preRemove($object)
    {
        $this->blockManager->get($object)->preRemove($object);
    }

    /**
     * {@inheritdoc}
     */
    public function postRemove($object)
    {
        $this->blockManager->get($object)->postRemove($object);
    }

    /**
     * @param \Sonata\BlockBundle\Block\BlockServiceManagerInterface $blockManager
     */
    public function setBlockManager(BlockServiceManagerInterface $blockManager)
    {
        $this->blockManager = $blockManager;
    }

    /**
     * @param \Sonata\Cache\CacheManagerInterface $cacheManager
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
            return array();
        }

        return array(
            'type'  => $this->getRequest()->get('type'),
        );
    }
}
