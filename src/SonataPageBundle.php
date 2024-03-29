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

namespace Sonata\PageBundle;

use Sonata\PageBundle\DependencyInjection\Compiler\CmfRouterCompilerPass;
use Sonata\PageBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Sonata\PageBundle\DependencyInjection\Compiler\PageServiceCompilerPass;
use Sonata\PageBundle\DependencyInjection\Compiler\TwigStringExtensionCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SonataPageBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new GlobalVariablesCompilerPass());
        $container->addCompilerPass(new PageServiceCompilerPass());
        $container->addCompilerPass(new CmfRouterCompilerPass());
        $container->addCompilerPass(new TwigStringExtensionCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
    }
}
