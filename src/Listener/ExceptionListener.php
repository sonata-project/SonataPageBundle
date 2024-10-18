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

namespace Sonata\PageBundle\Listener;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\PageServiceManagerInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class ExceptionListener
{
    private LoggerInterface $logger;

    /**
     * @param array<int, string> $httpErrorCodes An array of http error code routes
     */
    public function __construct(
        private SiteSelectorInterface $siteSelector,
        private CmsManagerSelectorInterface $cmsManagerSelector,
        private bool $debug,
        private Environment $twig,
        private PageServiceManagerInterface $pageServiceManager,
        private DecoratorStrategyInterface $decoratorStrategy,
        private array $httpErrorCodes,
        ?LoggerInterface $logger = null,
        private bool $status = false,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @return array<int, string>
     */
    public function getHttpErrorCodes(): array
    {
        return $this->httpErrorCodes;
    }

    public function hasErrorCode(int $statusCode): bool
    {
        return \array_key_exists($statusCode, $this->httpErrorCodes);
    }

    /**
     * @throws \RuntimeException      When site is not found, check your state database
     * @throws InternalErrorException When you do not configure page for http error code
     */
    public function getErrorCodePage(int $statusCode): PageInterface
    {
        if (!$this->hasErrorCode($statusCode)) {
            throw new InternalErrorException(\sprintf('There is not page configured to handle the status code %d', $statusCode));
        }

        $cms = $this->cmsManagerSelector->retrieve();
        $site = $this->siteSelector->retrieve();

        if (null === $site) {
            throw new \RuntimeException('No site available');
        }

        return $cms->getPageByRouteName($site, $this->httpErrorCodes[$statusCode]);
    }

    /**
     * @throws \Exception
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event->getThrowable() instanceof NotFoundHttpException && $this->cmsManagerSelector->isEditor()) {
            $pathInfo = $event->getRequest()->getPathInfo();

            // can only create a CMS page, so the '_route' must be null
            $creatable = null === $event->getRequest()->get('_route') && $this->decoratorStrategy->isRouteUriDecorable($pathInfo);

            if ($creatable) {
                $response = new Response($this->twig->render('@SonataPage/Page/create.html.twig', [
                    'pathInfo' => $pathInfo,
                    'site' => $this->siteSelector->retrieve(),
                    'creatable' => $creatable,
                ]), 404);

                $event->setResponse($response);
                $event->stopPropagation();

                return;
            }
        }

        if ($event->getThrowable() instanceof InternalErrorException) {
            $this->handleInternalError($event);
        } else {
            $this->handleNativeError($event);
        }
    }

    private function handleInternalError(ExceptionEvent $event): void
    {
        if (false === $this->debug) {
            $this->logger->error($event->getThrowable()->getMessage(), [
                'exception' => $event->getThrowable(),
            ]);

            return;
        }

        $content = $this->twig->render('@SonataPage/internal_error.html.twig', [
            'exception' => $event->getThrowable(),
        ]);

        $event->setResponse(new Response($content, 500));
    }

    private function handleNativeError(ExceptionEvent $event): void
    {
        if (true === $this->debug) {
            return;
        }

        if (true === $this->status) {
            return;
        }

        $this->status = true;

        $exception = $event->getThrowable();
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        $cmsManager = $this->cmsManagerSelector->retrieve();

        if (null !== $event->getRequest()->get('_route') && !$this->decoratorStrategy->isRouteNameDecorable($event->getRequest()->get('_route'))) {
            return;
        }

        if (!$this->decoratorStrategy->isRouteUriDecorable($event->getRequest()->getPathInfo())) {
            return;
        }

        if (!$this->hasErrorCode($statusCode)) {
            return;
        }

        $message = \sprintf('%s: %s (uncaught exception) at %s line %s', $exception::class, $exception->getMessage(), $exception->getFile(), $exception->getLine());

        $this->logException($exception, $exception, $message);

        try {
            $page = $this->getErrorCodePage($statusCode);
            $site = $page->getSite();

            $cmsManager->setCurrentPage($page);

            if (null !== $site) {
                $locale = $site->getLocale();

                if (null !== $locale && $locale !== $event->getRequest()->getLocale()) {
                    // Compare locales because Request returns the default one if null.

                    // If 404, LocaleListener from HttpKernel component of Symfony is not called.
                    // It uses the "_locale" attribute set by SiteSelectorInterface to set the request locale.
                    // So in order to translate messages, force here the locale with the site.
                    $event->getRequest()->setLocale($locale);
                }
            }

            $response = $this->pageServiceManager->execute($page, $event->getRequest(), [], new Response('', $statusCode));
        } catch (\Exception $e) {
            $this->logException($exception, $e);

            $event->setThrowable($e);
            $this->handleInternalError($event);

            return;
        }

        $event->setResponse($response);
    }

    private function logException(\Throwable $originalException, \Throwable $generatedException, ?string $message = null): void
    {
        if (null === $message) {
            $message = \sprintf('Exception thrown when handling an exception (%s: %s)', $generatedException::class, $generatedException->getMessage());
        }

        if (!$originalException instanceof HttpExceptionInterface || $originalException->getStatusCode() >= 500) {
            $this->logger->critical($message, ['exception' => $originalException]);
        } else {
            $this->logger->error($message, ['exception' => $originalException]);
        }
    }
}
