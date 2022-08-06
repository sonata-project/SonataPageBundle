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
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * This class return the correct manager instance:
 *   - sonata.page.cms.page if the user is an editor (ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT)
 *   - sonata.page.cms.snapshot if the user is a standard user.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class CmsManagerSelector implements CmsManagerSelectorInterface, BCLogoutHandlerInterface
{
    private CmsPageManager $cmsPageManager;
    private CmsSnapshotManager $cmsSnapshotManager;

    /**
     * @var AdminInterface<PageInterface>
     */
    private AdminInterface $pageAdmin;

    private TokenStorageInterface $tokenStorage;
    private RequestStack $requestStack;

    /**
     * @param AdminInterface<PageInterface> $pageAdmin
     */
    public function __construct(
        CmsPageManager $cmsPageManager,
        CmsSnapshotManager $cmsSnapshotManager,
        AdminInterface $pageAdmin,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack
    ) {
        $this->cmsPageManager = $cmsPageManager;
        $this->cmsSnapshotManager = $cmsSnapshotManager;
        $this->pageAdmin = $pageAdmin;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
    }

    public function retrieve()
    {
        return $this->isEditor() ? $this->cmsPageManager : $this->cmsSnapshotManager;
    }

    /**
     * The current order of event is not suitable for the selector to be call
     * by the router chain, so we need to use another mechanism. It is not perfect
     * but do the job for now.
     */
    public function isEditor()
    {
        $request = $this->requestStack->getCurrentRequest();

        return null !== $request &&
            $request->hasSession() &&
            false !== $request->getSession()->get('sonata/page/isEditor', false);
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        if (null !== $this->tokenStorage->getToken() &&
            $this->pageAdmin->isGranted('EDIT')) {
            $request = $event->getRequest();

            if ($request->hasSession()) {
                $request->getSession()->set('sonata/page/isEditor', true);
            }
        }
    }

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
