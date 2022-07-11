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

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\Service\BlockServiceInterface;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\Entity\BaseBlock;
use Sonata\PageBundle\Mapper\PageFormMapper;

/**
 * Admin class for shared Block model.
 *
 * @author Romain Mouillard <romain.mouillard@gmail.com>
 *
 * @final since sonata-project/page-bundle 3.26
 */
class SharedBlockAdmin extends BaseBlockAdmin
{
    /**
     * @var string
     */
    protected $classnameLabel = 'shared_block';

    public function getBaseRoutePattern()
    {
        return sprintf('%s/%s', parent::getBaseRoutePattern(), 'shared');
    }

    public function getBaseRouteName()
    {
        return sprintf('%s/%s', parent::getBaseRouteName(), 'shared');
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        // Filter on blocks without page and parents
        $rootAlias = current($query->getRootAliases());
        $query->andWhere($query->expr()->isNull($rootAlias.'.page'));
        $query->andWhere($query->expr()->isNull($rootAlias.'.parent'));

        return $query;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name')
            ->add('type')
            ->add('enabled', null, ['editable' => true])
            ->add('updatedAt');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var BaseBlock $block */
        $block = $this->getSubject();

        // New block
        if (null === $block->getId() && $this->hasRequest()) {
            $block->setType($this->request->get('type'));
        }

        $form
            ->with('form.field_group_general')
            ->add('name', null, ['required' => true])
            ->add('enabled')
            ->end();

        $form->with('form.field_group_options');

        $this->configureBlockFields($form, $block);

        $form->end();
    }

    private function configureBlockFields(FormMapper $form, BlockInterface $block): void
    {
        $blockType = $block->getType();

        if (null === $blockType || !$this->blockManager->has($blockType)) {
            return;
        }

        $service = $this->blockManager->get($block);

        if (!$service instanceof BlockServiceInterface) {
            throw new \RuntimeException(sprintf(
                'The block "%s" must implement %s',
                $blockType,
                BlockServiceInterface::class
            ));
        }

        if ($service instanceof EditableBlockService) {
            $blockMapper = new PageFormMapper($form);
            if ($block->getId() > 0) {
                $service->configureEditForm($blockMapper, $block);
            } else {
                $service->configureCreateForm($blockMapper, $block);
            }
        } else {
            @trigger_error(
                sprintf(
                    'Editing a block service that doesn\'t implement %s is deprecated since sonata-project/page-bundle 3.12.0 and will not be allowed with version 4.0.',
                    EditableBlockService::class
                ),
                \E_USER_DEPRECATED
            );

            if ($block->getId() > 0) {
                $service->buildEditForm($form, $block);
            } else {
                $service->buildCreateForm($form, $block);
            }
        }
    }
}
