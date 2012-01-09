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

        $cms  = $this->getCms();
        $page = $cms->getPageByUrl($pathInfo);

        // always render the last page version for the admin
        if (!$page && $this->get('security.context')->isGranted('ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT')) {
            $page = $cms->getPageByRouteName('catchAll');
            $cms->setCurrentPage($page);

            return $this->render('SonataPageBundle:Page:create.html.twig', array(
                'pathInfo'   => $pathInfo,
                'page'       => $page,
                'page_admin' => $this->get('sonata.page.admin.page'),
                'manager'    => $cms,
                'creatable'  => $cms->isRouteNameDecorable($this->get('request')->get('_route')) && $cms->isRouteUriDecorable($pathInfo)
            ));
        }

        if (!$page) {
            throw new NotFoundHttpException('The current url does not exist!');
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
            'httpErrorCodes' => $this->getCms()->getHttpErrorCodes(),
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

        $cms = $this->getCms();
        $httpErrorCodes = $cms->getHttpErrorCodes();

        if (!array_key_exists($code, $httpErrorCodes)) {
            throw new NotFoundHttpException(sprintf('The code "%s" is not set in the configuration', $code));
        }

        $page = $cms->getPageByName($httpErrorCodes[$code]);

        $cms->setCurrentPage($page);

        return $cms->renderPage($page);
    }

    /**
     * @return \Sonata\PageBundle\CmsManager\CmsManagerInterface
     */
    protected function getCms()
    {
        return $this->get('sonata.page.cms_manager_selector')->retrieve();
    }
}