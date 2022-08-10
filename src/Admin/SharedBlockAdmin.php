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
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\PageBundle\Mapper\PageFormMapper;
use Sonata\PageBundle\Model\PageBlockInterface;

/**
 * @author Romain Mouillard <romain.mouillard@gmail.com>
 */
final class SharedBlockAdmin extends BaseBlockAdmin
{
    protected $classnameLabel = 'shared_block';

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return sprintf('%s/%s', parent::generateBaseRoutePattern($isChildAdmin), 'shared');
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return sprintf('%s_%s', parent::generateBaseRouteName($isChildAdmin), 'shared');
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        \assert($query instanceof ProxyQuery);

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
        $block = $this->getSubject();

        // New block
        if (null === $block->getId() && $this->hasRequest()) {
            $block->setType($this->getRequest()->get('type'));
        }

        $form
            ->with('general')
                ->add('name', null, ['required' => true])
                ->add('enabled')
            ->end();

        $form->with('options');

        $this->configureBlockFields($form, $block);

        $form->end();
    }

    /**
     * @param FormMapper<PageBlockInterface> $form
     */
    private function configureBlockFields(FormMapper $form, BlockInterface $block): void
    {
        $blockType = $block->getType();

        if (null === $blockType || !$this->blockManager->has($blockType)) {
            return;
        }

        $service = $this->blockManager->get($block);

        if (!$service instanceof EditableBlockService) {
            throw new \RuntimeException(sprintf(
                'The block "%s" is not a valid %s',
                $blockType,
                EditableBlockService::class
            ));
        }

        $blockMapper = new PageFormMapper($form);

        if ($block->getId() > 0) {
            $service->configureEditForm($blockMapper, $block);
        } else {
            $service->configureCreateForm($blockMapper, $block);
        }
    }
}
