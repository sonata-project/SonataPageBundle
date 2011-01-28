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
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * PageExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class TextBlockService extends BaseBlockService
{

    public function execute($block, $page, Response $response = null)
    {

        return $this->render('SonataPageBundle:Block:block_core_text.twig.html', array(
            'block' => $block,
            'content' => $block->getSetting('content', '')
        ));
    }

    public function validateBlock($block)
    {
        // TODO: Implement validateBlock() method.
    }

    public function defineBlockGroupField($field_group, $block)
    {

        $field_group->add(new \Symfony\Component\Form\TextareaField('content'));
        
    }

}