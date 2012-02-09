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

class PageController extends Controller
{
    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    public function catchAllAction()
    {
        $pathInfo = $this->get('request')->getPathInfo();

        $site = $this->getSiteSelector()->retrieve();

        $cms  = $this->getCmsManager();

        try {
            $page = $cms->getPageByUrl($site, $pathInfo);
        } catch (PageNotFoundException $e) {
            $page = false;
        }

        // always render the last page version for the admin
        if (!$page && $this->get('security.context')->isGranted('ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT')) {

            try {
                $page = $cms->getPageByRouteName($site, 'catchAll');
            } catch (PageNotFoundException $e) {
                throw new InternalErrorException('The route catchAll is missing, please add the route to the routing file');
            }

            $cms->setCurrentPage($page);

            return $this->render('SonataPageBundle:Page:create.html.twig', array(
                'pathInfo'   => $pathInfo,
                'page'       => $page,
                'site'       => $site,
                'page_admin' => $this->get('sonata.page.admin.page'),
                'manager'    => $cms,
                'creatable'  => $cms->isRouteNameDecorable($this->get('request')->get('_route')) && $cms->isRouteUriDecorable($pathInfo)
            ));
        }

        if (!$page) {
            throw new PageNotFoundException('The current url does not exist!');
        }

        $cms->setCurrentPage($page);

        return $cms->renderPage($page);
    }

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    public function exceptionsListAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT')) {
            throw new AccessDeniedException();
        }

        return $this->render('SonataPageBundle:Exceptions:list.html.twig', array(
            'httpErrorCodes' => $this->getCmsManager()->getHttpErrorCodes(),
        ));
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException|\Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @param $code
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    public function exceptionEditAction($code)
    {
        if (!$this->get('security.context')->isGranted('ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT')) {
            throw new AccessDeniedException();
        }

        $cms = $this->getCmsManager();

        if (!$cms->hasErrorCode($code)) {
            throw new InternalErrorException(sprintf('The error code "%s" is not set in the configuration', $code));
        }

        try {
            $page = $cms->getErrorCodePage($this->getSiteSelector()->retrieve(), $code);
        } catch (PageNotFoundException $e) {
            throw new InternalErrorException('The requested error page does not exist, please run the sonata:page:update-core-routes command', null, $e);
        }

        $cms->setCurrentPage($page);

        return $cms->renderPage($page);
    }

    /**
     * @return \Sonata\PageBundle\CmsManager\CmsManagerInterface
     */
    protected function getCmsManager()
    {
        return $this->get('sonata.page.cms_manager_selector')->retrieve();
    }

    /**
     * @return \Sonata\PageBundle\Site\SiteSelectorInterface
     */
    protected function getSiteSelector()
    {
        return $this->get('sonata.page.site.selector');
    }
}