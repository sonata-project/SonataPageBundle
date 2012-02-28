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
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Generator\Mustache;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;

/**
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ContainerBlockService extends BaseBlockService
{
    /**
     * {@inheritdoc}
     */
    public function execute(BlockInterface $block, Response $response = null)
    {
        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());

        $response = $this->renderResponse('SonataPageBundle:Block:block_container.html.twig', array(
            'container' => $block,
            'settings'  => $settings,
        ), $response);

        $response->setContent(Mustache::replace($settings['layout'], array(
            'CONTENT' => $response->getContent()
        )));

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('enabled', null, array('required' => false));

        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('layout', 'textarea', array()),
                array('orientation', 'choice', array(
                    'choices' => array('block' => 'Block', 'left' => 'Left')
                )),
            )
        ));

        $formMapper->add('children', 'sonata_type_collection', array(), array(
            'edit'   => 'inline',
            'inline' => 'table',
            'sortable' => 'position'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Container (core)';
    }

    /**
     * {@inheritdoc}
     */
    function getDefaultSettings()
    {
        return array(
            'name'        => '',
            'layout'      => '{{ CONTENT }}',
            'orientation' => 'block',
        );
    }
}
