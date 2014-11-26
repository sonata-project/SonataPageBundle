<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Controller;

use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Exception\InternalErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Page controller
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class PageController extends Controller
{
    /**
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function exceptionsListAction()
    {
        if (!$this->getCmsManagerSelector()->isEditor()) {
            throw new AccessDeniedException();
        }

        return $this->render('SonataPageBundle:Exceptions:list.html.twig', array(
            'httpErrorCodes' => $this->getExceptionListener()->getHttpErrorCodes(),
        ));
    }

    /**
     * @throws InternalErrorException|AccessDeniedException
     *
     * @param string $code
     *
     * @return Response
     */
    public function exceptionEditAction($code)
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

        return $this->getPageServiceManager()->execute($page, $this->getRequest());
    }

    /**
     * @return \Sonata\PageBundle\Page\PageServiceManagerInterface
     */
    protected function getPageServiceManager()
    {
        return $this->get('sonata.page.page_service_manager');
    }

    /**
     * @return \Sonata\PageBundle\CmsManager\CmsManagerInterface
     */
    protected function getCmsManager()
    {
        return $this->getCmsManagerSelector()->retrieve();
    }

    /**
     * @return \Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface
     */
    protected function getCmsManagerSelector()
    {
        return $this->get('sonata.page.cms_manager_selector');
    }

    /**
     * @return \Sonata\PageBundle\Listener\ExceptionListener
     */
    public function getExceptionListener()
    {
        return $this->get('sonata.page.kernel.exception_listener');
    }
}
