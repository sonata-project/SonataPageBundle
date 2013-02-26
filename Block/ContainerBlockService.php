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

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Model\BlockInterface;

use Symfony\Component\HttpFoundation\Response;

/**
 * Render children pages
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ContainerBlockService extends BaseBlockService
{
    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('enabled');

        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('layout', 'textarea', array()),
                array('class', 'text', array('required' => false)),
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
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockInterface $block, Response $response = null)
    {
        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());

        $decorator = $this->getDecorator($settings['layout']);

        $response = $this->renderResponse($this->getTemplate(), array(
            'block'      => $block,
            'decorator'  => $decorator,
            'settings'   => $settings,
        ), $response);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSettings()
    {
        return array(
            'code'        => '',
            'layout'      => '{{ CONTENT }}',
            'class'       => ''
        );
    }

    /**
     * Returns the block service name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns a decorator object/array from the container layout setting
     *
     * @param string $layout
     *
     * @return mixed
     */
    protected function getDecorator($layout)
    {
        $key = '{{ CONTENT }}';
        if (strpos($layout, $key) === false) {
            return array();
        }

        $segments = explode($key, $layout);
        $decorator = array(
            'pre'  => isset($segments[0]) ? $segments[0] : '',
            'post' => isset($segments[1]) ? $segments[1] : '',
        );

        return $decorator;
    }

    /**
     * Returns template used for the block rendering
     *
     * @return string
     */
    protected function getTemplate()
    {
        return 'SonataPageBundle:Block:block_container.html.twig';
    }
}
