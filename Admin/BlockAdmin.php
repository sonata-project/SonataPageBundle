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
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\PageBundle\Model\PageInterface;

/**
 * Admin class for the Block model
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BlockAdmin extends BaseBlockAdmin
{
    protected $parentAssociationMapping = 'page';

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);

        $collection->add('savePosition', 'save-position');
        $collection->add('switchParent', 'switch-parent');
        $collection->add('composePreview', '{block_id}/compose_preview', array(
            'block_id' => null,
        ));
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

        $isComposer = $this->hasRequest() ? $this->getRequest()->get('composer', false) : false;
        $generalGroupOptions = $optionsGroupOptions = array();
        if ($isComposer) {
            $generalGroupOptions['class'] = 'hidden';
            $optionsGroupOptions['name']  = '';
        }

        $formMapper->with($this->trans('form.field_group_general'), $generalGroupOptions);

        if (!$isComposer) {
            $formMapper->add('name');
        } else {
            $formMapper->add('name', 'hidden');
        }

        $formMapper->end();

        $isContainerRoot = $block && in_array($block->getType(), array('sonata.page.block.container', 'sonata.block.service.container')) && !$this->hasParentFieldDescription();
        $isStandardBlock = $block && !in_array($block->getType(), array('sonata.page.block.container', 'sonata.block.service.container')) && !$this->hasParentFieldDescription();

        if ($isContainerRoot || $isStandardBlock) {

            $formMapper->with($this->trans('form.field_group_general'), $generalGroupOptions);

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
                    ),array(
                        'admin_code' => $this->getCode()
                    ));
            }

            if ($isComposer) {
                $formMapper->add('enabled', 'hidden', array('data' => true));
            } else {
                $formMapper->add('enabled');
            }

            if ($isStandardBlock) {
                $formMapper->add('position', 'integer');
            }

            $formMapper->end();

            $formMapper->with($this->trans('form.field_group_options'), $optionsGroupOptions);

            if ($block->getId() > 0) {
                $service->buildEditForm($formMapper, $block);
            } else {
                $service->buildCreateForm($formMapper, $block);
            }

            $formMapper->end();

        } else {

            $formMapper
                ->with($this->trans('form.field_group_options'), $optionsGroupOptions)
                ->add('type', 'sonata_block_service_choice', array(
                        'context' => 'sonata_page_bundle'
                    ))
                ->add('enabled')
                ->add('position', 'integer')
                ->end()
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentParameters()
    {
        $parameters = parent::getPersistentParameters();

        if ($composer = $this->getRequest()->get('composer')) {
            $parameters['composer'] = $composer;
        }

        return $parameters;
    }
}
