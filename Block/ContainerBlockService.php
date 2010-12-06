<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bundle\PageBundle\Block;

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

    public function execute($block)
    {

        return $this->render($block->getTemplate(), array(
             'block' => $block
        ));
    }

    public function validateBlock($block)
    {
        // TODO: Implement validateBlock() method.
    }

    public function getForm($block)
    {
        // TODO: Implement getForm() method.
    }

}