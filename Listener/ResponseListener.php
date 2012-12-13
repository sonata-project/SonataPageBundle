<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Listener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

use Sonata\PageBundle\Page\PageServiceManagerInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;


/**
 * This class redirect the onCoreResponse event to the correct
 * cms manager upon user permission
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ResponseListener
{
    /**
     * @var CmsManagerSelectorInterface
     */
    protected $cmsSelector;

    /**
     * @var PageServiceManagerInterface
     */
    protected $pageServiceManager;

    /**
     * @var DecoratorStrategyInterface
     */
    protected $decoratorStrategy;

    /**
     * @param CmsManagerSelectorInterface $cmsSelector        CMS manager selector
     * @param PageServiceManagerInterface $pageServiceManager Page service manager
     * @param DecoratorStrategyInterface  $decoratorStrategy  Decorator strategy
     */
    public function __construct(CmsManagerSelectorInterface $cmsSelector,
                                PageServiceManagerInterface $pageServiceManager,
                                DecoratorStrategyInterface $decoratorStrategy)
    {
        $this->cmsSelector        = $cmsSelector;
        $this->pageServiceManager = $pageServiceManager;
        $this->decoratorStrategy  = $decoratorStrategy;
    }

    /**
     * Filter the `core.response` event to decorate the action
     *
     * @param FilterResponseEvent $event
     *
     * @return void
     *
     * @throws InternalErrorException
     */
    public function onCoreResponse(FilterResponseEvent $event)
    {
        $cms = $this->cmsSelector->retrieve();

        $response = $event->getResponse();
        $request  = $event->getRequest();

        if ($this->cmsSelector->isEditor()) {
            $response->setPrivate();

            if (!$request->cookies->has('sonata_page_is_editor')) {
                $response->headers->setCookie(new Cookie('sonata_page_is_editor', 1));
            }
        }

        if (!$this->decoratorStrategy->isDecorable($event->getRequest(), $event->getRequestType(), $response)) {
            return;
        }

        if (!$this->cmsSelector->isEditor() && $request->cookies->has('sonata_page_is_editor')) {
            $response->headers->clearCookie('sonata_page_is_editor');
        }

        $page = $cms->getCurrentPage();

        if (!$page) {
            throw new InternalErrorException('No page instance available for the url, run the sonata:page:update-core-routes and sonata:page:create-snapshots commands');
        }

        // only decorate hybrid page or page with decorate = true
        if (!$page->isHybrid() || !$page->getDecorate()) {
            return;
        }

        $parameters = array('content' => $response->getContent());
        $response = $this->pageServiceManager->execute($page, $request, $parameters, $response);

        if (!$this->cmsSelector->isEditor() && $page->isCms()) {
            $response->setTtl($page->getTtl());
        }

        $event->setResponse($response);
    }
}