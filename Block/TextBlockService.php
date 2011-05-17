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
use Symfony\Component\Form\Form;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;

/**
 * PageExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class TextBlockService extends BaseBlockService
{

    public function execute(BlockInterface $block, PageInterface $page, Response $response = null)
    {
        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());

        return $this->render('SonataPageBundle:Block:block_core_text.html.twig', array(
            'block'     => $block,
            'settings'  => $settings
        ));
    }

    public function validateBlock(BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    public function buildCreateForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('content');
    }

    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('content');
    }

    public function getName()
    {
        return 'Text (core)';
    }

    /**
     * Returns the default options link to the service
     *
     * @return array
     */
    function getDefaultSettings()
    {
        return array('content' => 'Insert your custom content here');
    }
}