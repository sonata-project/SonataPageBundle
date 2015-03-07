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

use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\Page\PageServiceManagerInterface;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;

use Symfony\Component\Templating\EngineInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * ExceptionListener.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ExceptionListener
{
    /**
     * @var SiteSelectorInterface
     */
    protected $siteSelector;

    /**
     * @var CmsManagerSelectorInterface
     */
    protected $cmsManagerSelector;

    /**
     * @var boolean
     */
    protected $debug;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var PageServiceManagerInterface
     */
    protected $pageServiceManager;

    /**
     * @var DecoratorStrategyInterface
     */
    protected $decoratorStrategy;

    /**
     * @var array
     */
    protected $httpErrorCodes;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * @var boolean
     */
    protected $status;

    /**
     * Constructor
     *
     * @param SiteSelectorInterface       $siteSelector       Site selector
     * @param CmsManagerSelectorInterface $cmsManagerSelector CMS Manager selector
     * @param boolean                     $debug              Debug mode
     * @param EngineInterface             $templating         Templating engine
     * @param PageServiceManagerInterface $pageServiceManager Page service manager
     * @param DecoratorStrategyInterface  $decoratorStrategy  Decorator strategy
     * @param array                       $httpErrorCodes     An array of http error codes' routes
     * @param LoggerInterface|null        $logger             Logger instance
     */
    public function __construct(SiteSelectorInterface $siteSelector,
                                CmsManagerSelectorInterface $cmsManagerSelector,
                                $debug,
                                EngineInterface $templating,
                                PageServiceManagerInterface $pageServiceManager,
                                DecoratorStrategyInterface $decoratorStrategy,
                                array $httpErrorCodes,
                                LoggerInterface $logger = null)
    {
        $this->siteSelector       = $siteSelector;
        $this->cmsManagerSelector = $cmsManagerSelector;
        $this->debug              = $debug;
        $this->templating         = $templating;
        $this->pageServiceManager = $pageServiceManager;
        $this->decoratorStrategy  = $decoratorStrategy;
        $this->httpErrorCodes     = $httpErrorCodes;
        $this->logger             = $logger;
    }

    /**
     * Returns list of http error codes managed.
     *
     * @return array
     */
    public function getHttpErrorCodes()
    {
        return $this->httpErrorCodes;
    }

    /**
     * Returns true if the http error code is defined.
     *
     * @param integer $statusCode
     *
     * @return bool
     */
    public function hasErrorCode($statusCode)
    {
        return array_key_exists($statusCode, $this->httpErrorCodes);
    }

    /**
     * Returns a fully loaded page from a route name by the http error code throw.
     *
     * @param integer $statusCode
     *
     * @return \Sonata\PageBundle\Model\PageInterface
     *
     * @throws \RuntimeException      When site is not found, check your state database
     * @throws InternalErrorException When you do not configure page for http error code
     */
    public function getErrorCodePage($statusCode)
    {
        if (!$this->hasErrorCode($statusCode)) {
            throw new InternalErrorException(sprintf('There is not page configured to handle the status code %d', $statusCode));
        }

        $cms  = $this->cmsManagerSelector->retrieve();
        $site = $this->siteSelector->retrieve();

        if (!$site) {
            throw new \RuntimeException('No site available');
        }

        return $cms->getPageByRouteName($site, $this->httpErrorCodes[$statusCode]);
    }

    /**
     * Handles a kernel exception
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @throws \Exception
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($event->getException() instanceof NotFoundHttpException && $this->cmsManagerSelector->isEditor()) {
            $pathInfo = $event->getRequest()->getPathInfo();

            // can only create a CMS page, so the '_route' must be null
            $creatable = !$event->getRequest()->get('_route') && $this->decoratorStrategy->isRouteUriDecorable($pathInfo);

            if ($creatable) {
                $response = new Response($this->templating->render('SonataPageBundle:Page:create.html.twig', array(
                    'pathInfo'   => $pathInfo,
                    'site'       => $this->siteSelector->retrieve(),
                    'creatable'  => $creatable
                )), 404);

                $event->setResponse($response);
                $event->stopPropagation();

                return;
            }
        }

        if ($event->getException() instanceof InternalErrorException) {
            $this->handleInternalError($event);
        } else {
            $this->handleNativeError($event);
        }
    }

    /**
     * Handles an internal error
     *
     * @param GetResponseForExceptionEvent $event
     */
    private function handleInternalError(GetResponseForExceptionEvent $event)
    {
        $content = $this->templating->render('SonataPageBundle::internal_error.html.twig', array(
            'exception' => $event->getException()
        ));

        $event->setResponse(new Response($content, 500));
    }

    /**
     * Handles a native error
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @throws mixed
     */
    private function handleNativeError(GetResponseForExceptionEvent $event)
    {
        if (true === $this->debug) {
            return;
        }

        if (true === $this->status) {
            return;
        }

        $this->status = true;

        $exception  = $event->getException();
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        $cmsManager = $this->cmsManagerSelector->retrieve();

        if ($event->getRequest()->get('_route') && !$this->decoratorStrategy->isRouteNameDecorable($event->getRequest()->get('_route'))) {
            return;
        }

        if (!$this->decoratorStrategy->isRouteUriDecorable($event->getRequest()->getPathInfo())) {
            return;
        }

        if (!$this->hasErrorCode($statusCode)) {
            return;
        }

        $message = sprintf('%s: %s (uncaught exception) at %s line %s', get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine());

        $this->logException($exception, $exception, $message);

        try {
            $page = $this->getErrorCodePage($statusCode);

            $cmsManager->setCurrentPage($page);

            if ($page->getSite()->getLocale() !== $event->getRequest()->getLocale()) {
                // Compare locales because Request returns the default one if null.

                // If 404, LocaleListener from HttpKernel component of Symfony is not called.
                // It uses the "_locale" attribute set by SiteSelectorInterface to set the request locale.
                // So in order to translate messages, force here the locale with the site.
                $event->getRequest()->setLocale($page->getSite()->getLocale());
            }

            $response = $this->pageServiceManager->execute($page, $event->getRequest(), array(), new Response('', $statusCode));
        } catch (\Exception $e) {
            $this->logException($exception, $e);

            $event->setException($e);
            $this->handleInternalError($event);

            return;
        }

        $event->setResponse($response);
    }

    /**
     * Logs exceptions
     *
     * @param \Exception  $originalException  Original exception that called the listener
     * @param \Exception  $generatedException Generated exception
     * @param string|null $message            Message to log
     */
    private function logException(\Exception $originalException, \Exception $generatedException, $message = null)
    {
        if (!$message) {
            $message = sprintf('Exception thrown when handling an exception (%s: %s)', get_class($generatedException), $generatedException->getMessage());
        }

        if (null !== $this->logger) {
            if (!$originalException instanceof HttpExceptionInterface || $originalException->getStatusCode() >= 500) {
                $this->logger->crit($message, array( 'exception' => $originalException ));
            } else {
                $this->logger->err($message, array( 'exception' => $originalException ));
            }
        } else {
            error_log($message);
        }
    }
}
