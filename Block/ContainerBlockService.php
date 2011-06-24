<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\PageBundle\Block;

use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Generator\Mustache;
use Sonata\AdminBundle\Validator\ErrorElement;

/**
 * PageExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ContainerBlockService extends BaseBlockService
{

    /**
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function execute(BlockInterface $block, PageInterface $page, Response $response = null)
    {
        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());

        $response = $this->renderResponse('SonataPageBundle:Block:block_container.html.twig', array(
            'container' => $block,
            'manager'   => $this->manager,
            'page'      => $page,
        ), $response);

        $response->setContent(Mustache::replace($settings['layout'], array(
            'CONTENT' => $response->getContent()
        )));

        return $response;
    }

    /**
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('enabled', array('required' => false));

        $formMapper->addType('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('layout', 'textarea', array()),
            )
        ));

        $formMapper->add('children', array(), array(
            'edit'   => 'inline',
            'inline' => 'table',
            'sortable' => 'position'
        ));
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    public function buildCreateForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('enabled', array('required' => false));

        $formMapper->addType('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('layout', 'textarea', array()),
            )
        ));

        $formMapper->add('children', array(), array(
            'edit'   => 'inline',
            'inline' => 'table',
            'sortable' => 'position'
        ));
    }

    public function getName()
    {
        return 'Container (core)';
    }

    /**
     * Returns the default options link to the service
     *
     * @return array
     */
    function getDefaultSettings()
    {
        return array('layout' => '{{ CONTENT }}');
    }
}