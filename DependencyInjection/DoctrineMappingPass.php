<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\DependencyInjection;

use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register Doctrine assocations mapping
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class DoctrineMappingPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $collector = DoctrineCollector::getInstance();
        $parameterBag = $container->getParameterBag();

        $pageClass      = $parameterBag->get('sonata.page.page.class');
        $blockClass     = $parameterBag->get('sonata.page.block.class');
        $snpashotClass  = $parameterBag->get('sonata.page.snapshot.class');
        $siteClass      = $parameterBag->get('sonata.page.site.class');

        $collector->addAssociation($pageClass, 'mapOneToMany', array(
            'fieldName'     => 'children',
            'targetEntity'  => $pageClass,
            'cascade'       => array(
                'remove',
                'persist',
                'refresh',
                'merge',
                'detach',
             ),
            'mappedBy'      => 'parent',
            'orphanRemoval' => false,
            'orderBy'       => array(
                'position'  => 'ASC',
            ),
        ));

        $collector->addAssociation($pageClass, 'mapOneToMany', array(
            'fieldName'     => 'blocks',
            'targetEntity'  => $blockClass,
            'cascade' => array(
                'remove',
                'persist',
                'refresh',
                'merge',
                'detach',
            ),
            'mappedBy'      => 'page',
            'orphanRemoval' => false,
            'orderBy'       => array(
                'position'  => 'ASC',
            ),
        ));

        $collector->addAssociation($pageClass, 'mapOneToOne', array(
            'fieldName'     => 'site',
            'targetEntity'  => $siteClass,
            'cascade'       => array(
                'persist',
            ),
            'mappedBy'      => NULL,
            'inversedBy'    => NULL,
            'joinColumns'   => array(
                array(
                    'name'  => 'site_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($pageClass, 'mapOneToOne', array(
            'fieldName'     => 'parent',
            'targetEntity'  => $pageClass,
            'cascade'       => array(
                 'persist',
            ),
            'mappedBy'      => NULL,
            'inversedBy'    => NULL,
            'joinColumns'   => array(
                array(
                    'name'  => 'parent_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($pageClass, 'mapOneToMany', array(
             'fieldName' => 'sources',
             'targetEntity' => $pageClass,
             'cascade' => array(
                 'remove',
                 'persist',
                 'refresh',
                 'merge',
                 'detach',
             ),
             'mappedBy' => 'target',
             'orphanRemoval' => false,
        ));

        $collector->addAssociation($pageClass, 'mapOneToOne', array(
            'fieldName' => 'target',
            'targetEntity' => $pageClass,
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => NULL,
            'inversedBy' => NULL,
            'joinColumns' => array(
                array(
                    'name' => 'target_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($blockClass, 'mapOneToMany', array(
            'fieldName' => 'children',
            'targetEntity' => $blockClass,
            'cascade' => array(
                'remove',
                'persist',
            ),
            'mappedBy' => 'parent',
            'orphanRemoval' => true,
            'orderBy' => array(
                'position' => 'ASC',
            ),
        ));

        $collector->addAssociation($blockClass, 'mapOneToOne', array(
            'fieldName' => 'parent',
            'targetEntity' => $blockClass,
            'cascade' => array(
            ),
            'mappedBy' => NULL,
            'inversedBy' => NULL,
            'joinColumns' => array(
                array(
                    'name' => 'parent_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($blockClass, 'mapOneToOne', array(
            'fieldName' => 'page',
            'targetEntity' => $pageClass,
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => NULL,
            'inversedBy' => NULL,
            'joinColumns' => array(
                array(
                    'name' => 'page_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));


        $collector->addAssociation($snpashotClass, 'mapOneToOne', array(
            'fieldName'     => 'site',
            'targetEntity'  => $siteClass,
            'cascade'       => array(
                'persist',
            ),
            'mappedBy'      => NULL,
            'inversedBy'    => NULL,
            'joinColumns'   => array(
                array(
                    'name'      => 'site_id',
                    'referencedColumnName' => 'id',
                    'onDelete'  => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($snpashotClass, 'mapOneToOne', array(
            'fieldName'     => 'page',
            'targetEntity'  => $pageClass,
            'cascade' => array(
                'remove',
                'persist',
                'refresh',
                'merge',
                'detach',
            ),
            'mappedBy'      => NULL,
            'inversedBy'    => NULL,
            'joinColumns'   => array(
                array(
                    'name' => 'page_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));
    }
}
