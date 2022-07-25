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

namespace Sonata\PageBundle\CmsManager;

use Symfony\Component\Security\Http\Authentication\AuthenticatorManager;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * TODO: Simplify this when dropping support for Symfony 4.
 *
 * @psalm-suppress UndefinedClass
 */
if (class_exists(AuthenticatorManager::class) || !interface_exists(LogoutHandlerInterface::class)) {
    /** @psalm-suppress UnrecognizedStatement */
    interface BCLogoutHandlerInterface
    {
    }
} else {
    /** @psalm-suppress UnrecognizedStatement */
    interface BCLogoutHandlerInterface extends LogoutHandlerInterface
    {
    }
}
