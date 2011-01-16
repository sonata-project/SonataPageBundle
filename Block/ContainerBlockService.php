<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bundle\Sonata\PageBundle\Block;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * PageExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ContainerBlockService extends BaseBlockService
{

    public function execute($block, $page)
    {

        return $this->container->get('controller_resolver')->render('SonataPageBundle:Page:renderContainer', array(
            'attributes' => array(
                'name'              => $block->getSetting('name'),
                'page'              => $page,
                'parent_container'  => $block
            ),
            
        ));
    }

    public function validateBlock($block)
    {
        // TODO: Implement validateBlock() method.
    }

    public function defineBlockGroupField($field_group, $block)
    {
        
    }

}