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

use Sonata\BlockBundle\Block\BlockContextManager as BaseBlockContextManager;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockContextManager extends BaseBlockContextManager
{
    /**
     * @param OptionsResolver $optionsResolver
     * @param BlockInterface  $block
     */
    protected function configureSettings(OptionsResolver $optionsResolver, BlockInterface $block)
    {
        parent::configureSettings($optionsResolver, $block);

        $optionsResolver->setDefaults(array(
            'manager' => false,
            'page_id' => false,
        ));

        $optionsResolver->addAllowedTypes(array(
            'manager' => array('string', 'bool'),
            'page_id' => array('int', 'string', 'bool'),
        ));

        $optionsResolver->setRequired(array(
            'manager',
            'page_id',
        ));
    }
}
