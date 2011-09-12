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
use Sonata\PageBundle\Generator\Mustache;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;

/**
 * PageExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ActionBlockService extends BaseBlockService
{
    private $kernel;

    /**
     * @param $name
     * @param \Symfony\Component\Templating\EngineInterface $templating
     * @param \Symfony\Component\HttpKernel\HttpKernelInterface $kernel
     */
    public function __construct($name, EngineInterface $templating, HttpKernelInterface $kernel)
    {
        parent::__construct($name, $templating);

        $this->kernel = $kernel;
    }

    /**
     * @throws \Exception
     * @param \Sonata\PageBundle\CmsManager\CmsManagerInterface $manager
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     * @return string
     */
    public function execute(CmsManagerInterface $manager, BlockInterface $block, PageInterface $page, Response $response = null)
    {
        $parameters = (array)json_decode($block->getSetting('parameters'), true);
        $parameters = array_merge($parameters, array('_block' => $block, '_page' => $page));

        $settings = array_merge($this->getDefaultSettings(), (array)$block->getSettings());
        try {
            $actionContent = $this->kernel->render($settings['action'], $parameters);
        } catch (\Exception $e) {
            throw $e;
        }

        $content = Mustache::replace($block->getSetting('layout'), array(
            'CONTENT' => $actionContent
        ));

        return $this->renderResponse('SonataPageBundle:Block:block_core_action.html.twig', array(
            'content'   => $content,
            'block'     => $block,
            'page'      => $page,
        ), $response);
    }

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerInterface $manager
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    public function validateBlock(CmsManagerInterface $manager, ErrorElement $errorElement, BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerInterface $manager
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return void
     */
    public function buildEditForm(CmsManagerInterface $manager, FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('layout', 'textarea', array()),
                array('action', 'text', array()),
                array('parameters', 'text', array()),
            )
        ));
    }

    /**
     * @return string
     */
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