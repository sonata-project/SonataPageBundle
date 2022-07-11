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

namespace Sonata\PageBundle\Controller;

use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Listener\ExceptionListener;
use Sonata\PageBundle\Page\PageServiceManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Page controller.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @final since sonata-project/page-bundle 3.26
 */
class PageController extends AbstractController
{
    /**
     * @throws AccessDeniedException
     */
    public function exceptionsList(): Response
    {
        if (!$this->getCmsManagerSelector()->isEditor()) {
            throw new AccessDeniedException();
        }

        return $this->render('@SonataPage/Exceptions/list.html.twig', [
            'httpErrorCodes' => $this->getExceptionListener()->getHttpErrorCodes(),
        ]);
    }

    /**
     * @throws InternalErrorException|AccessDeniedException
     */
    public function exceptionEdit(int $code): Response
    {
        $cms = $this->getCmsManager();

        if (!$this->getExceptionListener()->hasErrorCode($code)) {
            throw new InternalErrorException(sprintf('The error code "%s" is not set in the configuration', $code));
        }

        try {
            $page = $this->getExceptionListener()->getErrorCodePage($code);
        } catch (PageNotFoundException $e) {
            throw new InternalErrorException('The requested error page does not exist, please run the sonata:page:update-core-routes command', null, $e);
        }

        $cms->setCurrentPage($page);

        // NEXT_MAJOR: remove the usage of $this->getRequestObject() by injecting the request action method in 4.0 (BC break)
        return $this->getPageServiceManager()->execute($page, $this->getRequestObject());
    }

    public function getExceptionListener(): ExceptionListener
    {
        return $this->get('sonata.page.kernel.exception_listener');
    }

    protected function getPageServiceManager(): PageServiceManagerInterface
    {
        return $this->get('sonata.page.page_service_manager');
    }

    protected function getCmsManager(): CmsManagerInterface
    {
        return $this->getCmsManagerSelector()->retrieve();
    }

    protected function getCmsManagerSelector(): CmsManagerSelectorInterface
    {
        return $this->get('sonata.page.cms_manager_selector');
    }

    private function getRequestObject(): Request
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }
}
