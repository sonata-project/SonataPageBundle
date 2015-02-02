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

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\PageBundle\Entity\BaseBlock;

/**
 * Admin class for shared Block model
 *
 * @author Romain Mouillard <romain.mouillard@gmail.com>
 */
class SharedBlockAdmin extends BaseBlockAdmin
{
    protected $classnameLabel = 'shared_block';

    /**
     * {@inheritDoc}
     */
    public function getBaseRoutePattern()
    {
        return sprintf('%s/%s', parent::getBaseRoutePattern(), 'shared');
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseRouteName()
    {
        return sprintf('%s/%s', parent::getBaseRouteName(), 'shared');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('type')
            ->add('enabled', null, array('editable' => true))
            ->add('updatedAt')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var BaseBlock $block */
        $block = $this->getSubject();

        // New block
        if ($block->getId() === null) {
            $block->setType($this->request->get('type'));
        }

        $formMapper
            ->with($this->trans('form.field_group_general'))
                ->add('name', null, array('required' => true))
                ->add('enabled')
            ->end();

        $formMapper->with($this->trans('form.field_group_options'));

        /** @var BaseBlockService $service */
        $service = $this->blockManager->get($block);

        if ($block->getId() > 0) {
            $service->buildEditForm($formMapper, $block);
        } else {
            $service->buildCreateForm($formMapper, $block);
        }

        $formMapper->end();
    }

    /**
     * {@inheritDoc)
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        // Filter on blocks without page and parents
        $query->andWhere($query->expr()->isNull($query->getRootAlias() . '.page'));
        $query->andWhere($query->expr()->isNull($query->getRootAlias() . '.parent'));

        return $query;
    }
}
