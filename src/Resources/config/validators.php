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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sonata\PageBundle\Validator\UniqueUrlValidator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.page.validator.unique_url', UniqueUrlValidator::class)
            ->tag('validator.constraint_validator', ['alias' => 'sonata.page.validator.unique_url'])
            ->args([
                service('sonata.page.manager.page'),
            ]);
};
