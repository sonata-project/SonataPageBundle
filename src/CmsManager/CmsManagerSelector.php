<?php

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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * This class return the correct manager instance :
 *   - sonata.page.cms.page if the user is an editor (ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT)
 *   - sonata.page.cms.snapshot if the user is a standard user.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CmsManagerSelector implements CmsManagerSelectorInterface, LogoutHandlerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
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
     * {@inheritdoc}
     */
    public function isEditor()
    {
        /*
         * The current order of event is not suitable for the selector to be call
         * by the router chain, so we need to use another mechanism. It is not perfect
         * but do the job for now.
         */

        $request = $this->getRequest();
        $session = $this->getSession();
        $sessionAvailable = ($request && $request->hasPreviousSession()) || $session->isStarted();

        return $sessionAvailable && $session->get('sonata/page/isEditor', false);
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        if ($this->container->get('security.token_storage')->getToken() &&
            $this->container->get('sonata.page.admin.page')->isGranted('EDIT')) {
            $this->getSession()->set('sonata/page/isEditor', true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $this->getSession()->set('sonata/page/isEditor', false);
        if ($request->cookies->has('sonata_page_is_editor')) {
            $response->headers->clearCookie('sonata_page_is_editor');
        }
    }

    /**
     * @return SessionInterface
     */
    private function getSession()
    {
        return $this->container->get('session');
    }

    /**
     * @return null|Request
     */
    private function getRequest()
    {
        if ($this->container->has('request_stack')) {
            return $this->container->get('request_stack')->getCurrentRequest();
        }

        if ($this->container->isScopeActive('request')) {
            return $this->container->get('request');
        }

        return;
    }
}
