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

namespace Sonata\PageBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Sonata\PageBundle\DependencyInjection\Compiler\CmfRouterCompilerPass;
use Symfony\Cmf\Component\Routing\ChainRouter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CmfRouterAutoRegisterTest extends AbstractCompilerPassTestCase
{
    /**
     * @dataProvider providerRoutedAutoRegister
     */
    public function testRouterAutoRegister($enabled, $priority): void
    {
        $this->container->setParameter('sonata.page.router_auto_register.enabled', $enabled);
        $this->container->setParameter('sonata.page.router_auto_register.priority', $priority);
        $this->registerService('cmf_routing.router', ChainRouter::class);
        $this->compile();

        $router = $this->container->getDefinition('cmf_routing.router');
        foreach ($router->getMethodCalls() as $methodCall) {
            [$method, $arguments] = $methodCall;

            if ('add' !== $method) {
                continue;
            }
            [$reference, $weight] = $arguments;
            if ($reference instanceof Reference && 'sonata.page.router' === $reference->__toString()) {
                if ($enabled) {
                    $this->assertSame($priority, $weight);
                    break;
                }
                $this->fail('"sonata.page.router" service should not be auto registered');
            }
        }
    }

    public function providerRoutedAutoRegister(): array
    {
        return [
            'enabled router' => [true, 42],
            'disabled router' => [false, 84],
        ];
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CmfRouterCompilerPass());
    }
}
