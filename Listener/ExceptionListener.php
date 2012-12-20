<?php

namespace Sonata\PageBundle\Listener;

use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\CmsManager\PageRendererInterface;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
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
    protected $cmsManagerSelector;

    protected $siteSelector;

    protected $debug;

    protected $logger;

    protected $status;

    protected $templating;

    protected $pageRenderer;

    protected $decoratorStrategy;

    protected $httpErrorCodes;

    /**
     * Constructor.
     *
     * @param SiteSelectorInterface       $siteSelector       Site selector
     * @param CmsManagerSelectorInterface $cmsManagerSelector Cms manager selector
     * @param boolean                     $debug              Debug mode
     * @param EngineInterface             $templating         Template engine
     * @param PageRendererInterface       $pageRenderer       Page render
     * @param DecoratorStrategyInterface  $decoratorStrategy  Decorator strategy
     * @param array                       $httpErrorCodes     Http error codes managed
     * @param null|LoggerInterface        $logger             Logger
     */
    public function __construct(SiteSelectorInterface $siteSelector, CmsManagerSelectorInterface $cmsManagerSelector, $debug, EngineInterface $templating, PageRendererInterface $pageRenderer, DecoratorStrategyInterface $decoratorStrategy, array $httpErrorCodes, LoggerInterface $logger = null)
    {
        $this->cmsManagerSelector = $cmsManagerSelector;
        $this->debug              = $debug;
        $this->logger             = $logger;
        $this->templating         = $templating;
        $this->siteSelector       = $siteSelector;
        $this->pageRenderer       = $pageRenderer;
        $this->decoratorStrategy  = $decoratorStrategy;
        $this->httpErrorCodes     = $httpErrorCodes;
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
     * Returns response error under the environment and debug mode.
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @return bool
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
     * Returns html page for critical error.
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @return void
     */
    private function handleInternalError(GetResponseForExceptionEvent $event)
    {
        $content = $this->templating->render('SonataPageBundle::internal_error.html.twig', array(
            'exception' => $event->getException()
        ));

        $event->setResponse(new Response($content, 500));
    }

    /**
     * Seeks the problem.
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @return void
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

            $response = $this->pageRenderer->render($page, array(), new Response('', $statusCode));
        }
        catch (\Exception $e) {
            $this->logException($exception, $e);

            $event->setException($e);
            $this->handleInternalError($event);

            return;
        }

        $event->setResponse($response);
    }

    /**
     * Log error.
     *
     * @param \Exception  $originalException
     * @param \Exception  $generatedException
     * @param null|string $message
     *
     * @return void
     */
    private function logException(\Exception $originalException, \Exception $generatedException, $message = null)
    {
        if (!$message) {
            $message = sprintf('Exception thrown when handling an exception (%s: %s)', get_class($generatedException), $generatedException->getMessage());
        }

        if (null !== $this->logger) {
            if (!$originalException instanceof HttpExceptionInterface || $originalException->getStatusCode() >= 500) {
                $this->logger->crit($message);
            } else {
                $this->logger->err($message);
            }
        } else {
            error_log($message);
        }
    }
}
