<?php

/*
 * This file is part of the Sonata project.
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
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * This class return the correct manager instance :
 *   - sonata.page.cms.page if the user is an editor (ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT)
 *   - sonata.page.cms.snapshot if the user is a standard user
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CmsManagerSelector implements CmsManagerSelectorInterface, LogoutHandlerInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
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
        /**
         * The current order of event is not suitable for the selector to be call
         * by the router chain, so we need to use another mechanism. It is not perfect
         * but do the job for now.
         */

        return $this->getSession()->get('sonata/page/isEditor', false);
    }

    /**
     * @return SessionInterface
     */
    private function getSession()
    {
        return $this->container->get('session');
    }

    /**
     * @return SecurityContextInterface
     */
    private function getSecurityContext()
    {
        return $this->container->get('security.context');
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        if ($this->getSecurityContext()->getToken() && $this->getSecurityContext()->isGranted('ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT')) {
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
}
