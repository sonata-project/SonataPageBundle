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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * This class return the correct manager instance :
 *   - sonata.page.cms.page if the user is an editor (ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT)
 *   - sonata.page.cms.snapshot if the user is a standard user.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class CmsManagerSelector implements CmsManagerSelectorInterface
{
    private ContainerInterface $container;

    /**
     * @psalm-suppress ContainerDependency
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function retrieve()
    {
        if ($this->isEditor()) {
            $manager = $this->container->get('sonata.page.cms.page');
        } else {
            $manager = $this->container->get('sonata.page.cms.snapshot');
        }

        return $manager;
    }

    /**
     * The current order of event is not suitable for the selector to be call
     * by the router chain, so we need to use another mechanism. It is not perfect
     * but do the job for now.
     */
    public function isEditor()
    {
        $request = $this->getRequest();

        return $request->hasSession() && $request->getSession()->get('sonata/page/isEditor', false);
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        if ($this->container->get('security.token_storage')->getToken() &&
            $this->container->get('sonata.page.admin.page')->isGranted('EDIT')) {
            $request = $event->getRequest();

            if ($request->hasSession()) {
                $request->getSession()->set('sonata/page/isEditor', true);
            }
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

            if ($response !== null) {
                $response->headers->clearCookie('sonata_page_is_editor');
            }
        }
    }

    /**
     * @return Request|null
     */
    private function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }
}
