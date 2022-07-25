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
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractAdmin<PageBlockInterface>
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

    public function setBlockManager(BlockServiceManagerInterface $blockManager): void
    {
        $this->blockManager = $blockManager;
    }

    public function setContainerBlockTypes(array $containerBlockTypes): void
    {
        $this->containerBlockTypes = $containerBlockTypes;
    }

    public function preBatchAction(string $actionName, ProxyQueryInterface $query, array &$idx, bool $allElements = false): void
    {
        if ($this->isChild() && 'delete' === $actionName) {
            $parent = $this->getParent();
            $subject = $parent->getSubject();

            if ($subject instanceof PageInterface) {
                $subject->setEdited(true);
            }
        }

        parent::preBatchAction($actionName, $query, $idx, $allElements);
    }

    protected function preUpdate(object $object): void
    {
        if ($object->getPage() instanceof PageInterface) {
            $object->getPage()->setEdited(true);
        }
    }

    protected function prePersist(object $object): void
    {
        if ($object->getPage() instanceof PageInterface) {
            $object->getPage()->setEdited(true);
        }
    }

    protected function preRemove(object $object): void
    {
        $page = $object->getPage();

        if ($page instanceof PageInterface) {
            $page->setEdited(true);
        }
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

    protected function configureRoutes(RouteCollectionInterface $collection): void
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

    private function loadBlockDefaults(PageBlockInterface $block): PageBlockInterface
    {
        $blockType = $block->getType();

        if (null === $blockType || !$this->blockManager->has($blockType)) {
            return $block;
        }

        $service = $this->blockManager->get($block);

        $resolver = new OptionsResolver();
        // use new interface method whenever possible
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
