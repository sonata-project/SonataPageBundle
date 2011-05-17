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
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Templating\EngineInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Symfony\Bundle\FrameworkBundle\Util\Mustache;


/**
 * PageExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ActionBlockService extends BaseBlockService
{
    private $kernel;

    public function __construct($name, EngineInterface $templating, HttpKernelInterface $kernel)
    {
        parent::__construct($name, $templating);

        $this->kernel = $kernel;
    }

    /**
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return string
     */
    public function execute(BlockInterface $block, PageInterface $page, Response $response = null)
    {
        $params = array_merge($block->getSetting('parameters', array()), array('_block' => $block, '_page' => $page));

        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());
        try {
            $actionContent = $this->kernel->render($settings['action'], $params);
        } catch (\Exception $e) {
            throw $e;
        }

        $content = Mustache::renderString($block->getSetting('layout'), array(
            'CONTENT' => $actionContent
        ));

        return $this->render('SonataPageBundle:Block:block_core_action.html.twig', array(
            'content'   => $content,
            'block'     => $block,
            'page'      => $page,
        ), $response);
    }

    public function validateBlock(BlockInterface $block)
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
        $formMapper->add('action', array(), array('type' => 'string'));

        $formBuilder = $formMapper->getFormBuilder();
        $form = $formBuilder
            ->create('settings', 'collection')
            ->add('layout', 'text')
            ->add('action', 'text')
            ->add('parameters', 'text');

        $formMapper->addType($form);
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    public function buildCreateForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('action');

        $parameters = new Form('parameters');

        foreach ($block->getSetting('parameters', array()) as $name => $value) {
            $formMapper->add(new TextField($name));
        }

        $formMapper->add($parameters);
    }

    public function getName()
    {
        return 'Action (core)';
    }

    /**
     * Returns the default settings link to the service
     *
     * @return array
     */
    function getDefaultSettings()
    {
        return array(
            'layout'      => '{{ CONTENT }}',
            'action'      => 'SonataPageBundle:Block:empty',
            'parameters'  => '{}'
        );
    }
}