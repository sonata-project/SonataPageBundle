<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/*
 * This listener overrides Symfony's Symfony\Component\Security\Http\Firewall\ExceptionListener.
 *
 * Sonata's relativePath is implemented in the Router, but not in the Request. Because of this
 * Symfony's Firewall Exception Listener does not know about the relativePath and can not implement
 * it on it's own. If we tell the Request about the relativePath, problems will occur where it will
 * be applied twice (both by the Router and the Request).
 *
 * This override will take care of at least one of the problems, where the Symfony's targetPath
 * points to an uri without Sonata's relativePath.
 */
final class SecurityExceptionListener extends ExceptionListener
{
    use TargetPathTrait;

    private $providerKey;

    /**
     * {@inheritdoc}
     */
    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationTrustResolverInterface $trustResolver, HttpUtils $httpUtils, $providerKey, AuthenticationEntryPointInterface $authenticationEntryPoint = null, $errorPage = null, AccessDeniedHandlerInterface $accessDeniedHandler = null, LoggerInterface $logger = null, $stateless = false)
    {
        $this->providerKey = $providerKey;

        parent::__construct(
            $tokenStorage,
            $trustResolver,
            $httpUtils,
            $providerKey,
            $authenticationEntryPoint,
            $errorPage,
            $accessDeniedHandler,
            $logger,
            $stateless
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setTargetPath(Request $request)
    {
        // session isn't required when using HTTP basic authentication mechanism for example
        if ($request->hasSession() && $request->isMethodSafe(false) && !$request->isXmlHttpRequest()) {
            $this->saveTargetPath($request->getSession(), $this->providerKey, $request->getUriForPath($request->getRequestUri()));
        }
    }
}
