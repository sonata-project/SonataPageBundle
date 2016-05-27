<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Block;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\PageBundle\Model\Page;
use Sonata\PageBundle\Model\PageManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageListBlockService extends BaseBlockService
{
    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    /**
     * @param string               $name
     * @param EngineInterface      $templating
     * @param PageManagerInterface $pageManager
     */
    public function __construct($name, EngineInterface $templating, PageManagerInterface $pageManager)
    {
        parent::__construct($name, $templating);

        $this->pageManager = $pageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('title', 'text', array(
                    'label' => 'form.label_title',
                    'required' => false,
                )),
                array('mode', 'choice', array(
                    'label' => 'form.label_mode',
                    'choices' => array(
                        'public' => 'form.choice_public',
                        'admin' => 'form.choice_admin',
                    ),
                )),
            ),
            'translation_domain' => 'SonataPageBundle',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $pageList = $this->pageManager->findBy(array(
            'routeName' => Page::PAGE_ROUTE_CMS_NAME,
        ));

        $systemElements = $this->pageManager->findBy(array(
            'url' => null,
            'parent' => null,
        ));

        return $this->renderResponse($blockContext->getTemplate(), array(
            'context' => $blockContext,
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'elements' => $pageList,
            'systemElements' => $systemElements,
        ), $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'mode' => 'public',
            'title' => 'List Pages',
            'template' => 'SonataPageBundle:Block:block_pagelist.html.twig',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (!is_null($code) ? $code : $this->getName()), false, 'SonataPageBundle', array(
            'class' => 'fa fa-home',
        ));
    }
}
