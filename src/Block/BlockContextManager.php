<?php

/*
 * This file is part of the Sonata Project package.
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
     * {@inheritdoc}
     */
    protected function configureSettings(OptionsResolver $optionsResolver, BlockInterface $block)
    {
        parent::configureSettings($optionsResolver, $block);

        $optionsResolver->setDefaults([
            'manager' => false,
            'page_id' => false,
        ]);

        $optionsResolver
            ->addAllowedTypes('manager', ['string', 'bool'])
            ->addAllowedTypes('page_id', ['int', 'string', 'bool'])
        ;

        $optionsResolver->setRequired([
            'manager',
            'page_id',
        ]);
    }
}
