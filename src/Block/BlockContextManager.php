<?php

declare(strict_types=1);

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

/**
 * NEXT_MAJOR: Do not extend from `BlockContextManager` since it will be final.
 *
 * @psalm-suppress InvalidExtendClass
 * @phpstan-ignore-next-line
 *
 * @final since sonata-project/page-bundle 3.x
 */
class BlockContextManager extends BaseBlockContextManager
{
    protected function configureSettings(OptionsResolver $optionsResolver, BlockInterface $block): void
    {
        parent::configureSettings($optionsResolver, $block);

        $optionsResolver->setDefaults([
            'manager' => false,
            'page_id' => false,
        ]);

        $optionsResolver
            ->addAllowedTypes('manager', ['string', 'bool'])
            ->addAllowedTypes('page_id', ['int', 'string', 'bool']);

        $optionsResolver->setRequired([
            'manager',
            'page_id',
        ]);
    }
}
