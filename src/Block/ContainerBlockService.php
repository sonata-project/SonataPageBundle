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

use Sonata\BlockBundle\Block\Service\ContainerBlockService as BaseContainerBlockService;
use Sonata\BlockBundle\Meta\Metadata;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * NEXT_MAJOR: Do not extend from `ContainerBlockService` since it will be final.
 *
 * Render children pages.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @psalm-suppress InvalidExtendClass
 * @phpstan-ignore-next-line
 *
 * @final since sonata-project/page-bundle 3.26
 */
class ContainerBlockService extends BaseContainerBlockService
{
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'code' => '',
            'layout' => '{{ CONTENT }}',
            'class' => '',
            'template' => '@SonataPage/Block/block_container.html.twig',
        ]);
    }

    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), $code ?? $this->getName(), false, 'SonataPageBundle', [
            'class' => 'fa fa-square-o',
        ]);
    }
}
