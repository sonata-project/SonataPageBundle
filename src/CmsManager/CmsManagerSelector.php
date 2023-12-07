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

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\PageBundle\Model\PageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * This class return the correct manager instance:
 *   - sonata.page.cms.page if the user is an editor (ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT)
 *   - sonata.page.cms.snapshot if the user is a standard user.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class CmsManagerSelector implements CmsManagerSelectorInterface
{
    /**
     * @param AdminInterface<PageInterface> $pageAdmin
     */
    public function __construct(
        private CmsPageManager $cmsPageManager,
        private CmsSnapshotManager $cmsSnapshotManager,
        private AdminInterface $pageAdmin,
        private TokenStorageInterface $tokenStorage,
        private RequestStack $requestStack
    ) {
    }

    public function retrieve(): CmsManagerInterface
    {
        return $this->isEditor() ? $this->cmsPageManager : $this->cmsSnapshotManager;
    }

    /**
     * The current order of event is not suitable for the selector to be call
     * by the router chain, so we need to use another mechanism. It is not perfect
     * but do the job for now.
     */
    public function isEditor(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        return null !== $request
            && $request->hasSession()
            && false !== $request->getSession()->get('sonata/page/isEditor', false);
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $this->handleLoginSuccess($event->getRequest());
    }

    /**
     * NEXT_MAJOR: Remove this class.
     *
     * @deprecated since sonata-project/page-bundle 4.7.0
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/page-bundle 4.7.0 and will be removed in 5.0.'
                . '  Use "%s()" instead.',
            __METHOD__,
            'onLoginSuccess'
        ), \E_USER_DEPRECATED);
        $this->handleLoginSuccess($event->getRequest());
    }

    private function handleLoginSuccess(Request $request): void
    {
        if (null !== $this->tokenStorage->getToken()
            && $this->pageAdmin->isGranted('EDIT')) {

            if ($request->hasSession()) {
                $request->getSession()->set('sonata/page/isEditor', true);
            }
        }
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function logout(Request $request, Response $response, TokenInterface $token): void
    {
        if ($request->hasSession()) {
            $request->getSession()->set('sonata/page/isEditor', false);
        }

        if ($request->cookies->has('sonata_page_is_editor')) {
            $response->headers->clearCookie('sonata_page_is_editor');
        }
    }

    public function onLogout(LogoutEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->hasSession()) {
            $request->getSession()->set('sonata/page/isEditor', false);
        }

        if ($request->cookies->has('sonata_page_is_editor')) {
            $response = $event->getResponse();

            if (null !== $response) {
                $response->headers->clearCookie('sonata_page_is_editor');
            }
        }
    }
}
