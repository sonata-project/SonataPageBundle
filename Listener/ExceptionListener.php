<?php

namespace Sonata\PageBundle\Listener;

use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
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
 * @author Fabien Potencier <fabien@symfony.com>
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
     * @param \Sonata\PageBundle\Site\SiteSelectorInterface $siteSelector
     * @param \Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface $cmsManagerSelector
     * @param $debug
     * @param \Symfony\Component\Templating\EngineInterface $templating
     * @param \Sonata\PageBundle\CmsManager\PageRendererInterface $pageRenderer
     * @param \Sonata\PageBundle\CmsManager\DecoratorStrategyInterface $decoratorStrategy
     * @param array $httpErrorCodes
     * @param null|\Symfony\Component\HttpKernel\Log\LoggerInterface $logger
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
     * {@inheritdoc}
     */
    public function getHttpErrorCodes()
    {
        return $this->httpErrorCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function hasErrorCode($statusCode)
    {
        return array_key_exists($statusCode, $this->httpErrorCodes);
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCodePage($statusCode)
    {
        if (!$this->hasErrorCode($statusCode)) {
            throw new InternalErrorException(sprintf('There is not page configured to handle the status code %d', $statusCode));
        }

        $cms = $this->cmsManagerSelector->retrieve();
        $site = $this->siteSelector->retrieve();

        return $cms->getPageByRouteName($site, $this->httpErrorCodes[$statusCode]);
    }

    /**
     * @throws \Exception
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     * @return bool
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($event->getException() instanceof InternalErrorException) {
            $this->handleInternalError($event);
        } else {
            $this->handleNativeError($event);
        }
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
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
     * @throws \Exception
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
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

        $exception = $event->getException();
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        $cmsManager = $this->cmsManagerSelector->retrieve();

        if (!$this->decoratorStrategy->isRequestDecorable($event->getRequest())) {
            return;
        }

        if (!$this->hasErrorCode($statusCode)) {
            return;
        }

        $message = sprintf('%s: %s (uncaught exception) at %s line %s', get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine());

        $this->logException($exception, $exception, $message);

        try {
            $page = $this->getErrorCodePage($statusCode);
        } catch (PageNotFoundException $e) {

            $this->handleInternalError($e);

            return;

        } catch (\Exception $e) {
            $this->logException($exception, $e);

            // re-throw the exception as this is a catch-all
            throw $exception;
        }

        $cmsManager->setCurrentPage($page);

        try {
            $response = $this->pageRenderer->render($page, array(), new Response('', $statusCode));
        } catch (\Exception $e) {
            $this->logException($exception, $e);

            // re-throw the exception as this is a catch-all
            throw $exception;
        }

        $event->setResponse($response);
    }

    /**
     * @param \Exception $originalException
     * @param \Exception $generatedException
     * @param null $message
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
