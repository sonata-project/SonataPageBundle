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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class PageController extends AbstractController
{
    public function __construct(
        private ExceptionListener $exceptionListener,
        private PageServiceManagerInterface $pageServiceManager,
        private CmsManagerSelectorInterface $cmsSelector,
    ) {
    }

    /**
     * @throws AccessDeniedException
     */
    public function exceptionsList(): Response
    {
        if (!$this->cmsSelector->isEditor()) {
            throw new AccessDeniedException();
        }

        return $this->render('@SonataPage/Exceptions/list.html.twig', [
            'httpErrorCodes' => $this->exceptionListener->getHttpErrorCodes(),
        ]);
    }

    /**
     * @throws InternalErrorException|AccessDeniedException
     */
    public function exceptionEdit(Request $request, int $code): Response
    {
        $cms = $this->cmsSelector->retrieve();

        if (!$this->exceptionListener->hasErrorCode($code)) {
            throw new InternalErrorException(\sprintf('The error code "%s" is not set in the configuration', $code));
        }

        try {
            $page = $this->exceptionListener->getErrorCodePage($code);
        } catch (PageNotFoundException $e) {
            throw new InternalErrorException('The requested error page does not exist, please run the sonata:page:update-core-routes command', 0, $e);
        }

        $cms->setCurrentPage($page);

        return $this->pageServiceManager->execute($page, $request);
    }
}
