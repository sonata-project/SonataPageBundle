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

namespace Sonata\PageBundle\Runtime;

use http\Exception\InvalidArgumentException;
use Sonata\PageBundle\Request\RequestFactory;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Runtime\Runner\Symfony\HttpKernelRunner;
use Symfony\Component\Runtime\RunnerInterface;
use Symfony\Component\Runtime\SymfonyRuntime;

class SonataPageRuntime extends SymfonyRuntime
{
    private string $multisite;

    /**
     * @param array<mixed> $options
     */
    public function __construct(array $options = [])
    {
        if (!isset($options['multisite'])) {
            throw new InvalidArgumentException('The option multisite is required');
        }

        $this->multisite = $options['multisite'];
        parent::__construct($options);
    }

    public function getRunner(?object $application): RunnerInterface
    {
        if ($application instanceof HttpKernelInterface) {
            return new HttpKernelRunner($application, RequestFactory::createFromGlobals($this->multisite));
        }

        return parent::getRunner($application);
    }
}
