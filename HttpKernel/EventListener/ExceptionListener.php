<?php

namespace Sonata\PageBundle\HttpKernel\EventListener;

use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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

    /**
     * @param \Sonata\PageBundle\Site\SiteSelectorInterface $siteSelector
     * @param \Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface $cmsManagerSelector
     * @param $debug
     * @param null|\Symfony\Component\HttpKernel\Log\LoggerInterface $logger
     */
    public function __construct(SiteSelectorInterface $siteSelector, CmsManagerSelectorInterface $cmsManagerSelector, $debug, LoggerInterface $logger = null)
    {
        $this->cmsManagerSelector = $cmsManagerSelector;
        $this->debug              = $debug;
        $this->logger             = $logger;
        $this->siteSelector       = $siteSelector;
    }

    /**
     * @throws \Exception
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     * @return bool
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (true === $this->debug) {
            return false;
        }

        if (true === $this->status) {
            return false;
        }

        $this->status = true;

        $exception = $event->getException();
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        if (null !== $this->logger) {
            $message = sprintf('%s: %s (uncaught exception) at %s line %s', get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine());

            if (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
                $this->logger->crit($message);
            } else {
                $this->logger->err($message);
            }
        } else {
            error_log(sprintf('Uncaught PHP Exception %s: "%s" at %s line %s', get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine()));
        }

        $cmsManager = $this->cmsManagerSelector->retrieve();
        $httpErrorCodes = $cmsManager->getHttpErrorCodes();

        if (!array_key_exists($statusCode, $httpErrorCodes)) {
            return false;
        }

        try {
            $page = $cmsManager->getPageByRouteName($this->siteSelector->retrieve(), $httpErrorCodes[$statusCode]);
        } catch (\Exception $e) {
            $message = sprintf('Exception thrown when handling an exception (%s: %s)', get_class($e), $e->getMessage());

            if (null !== $this->logger) {
                if (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
                    $this->logger->crit($message);
                } else {
                    $this->logger->err($message);
                }
            } else {
                error_log($message);
            }

            // re-throw the exception as this is a catch-all
            throw $exception;
        }

        if (!$page) {
            return false;
        }

        $cmsManager->setCurrentPage($page);

        try {
            $response = $cmsManager->renderPage($page);
        } catch (\Exception $e) {
            $message = sprintf('Exception thrown when handling an exception (%s: %s)', get_class($e), $e->getMessage());

            if (null !== $this->logger) {
                if (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
                    $this->logger->crit($message);
                } else {
                    $this->logger->err($message);
                }
            } else {
                error_log($message);
            }

            // re-throw the exception as this is a catch-all
            throw $exception;
        }

        $event->setResponse($response);
    }
}
