<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View as FOSRestView;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Raphaël Benitte <benitteraphael@gmail.com>
 */
class SiteController extends FOSRestController
{
    /**
     * @var SiteManagerInterface
     */
    protected $siteManager;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @param SiteManagerInterface $siteManager
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(SiteManagerInterface $siteManager, FormFactoryInterface $formFactory)
    {
        $this->siteManager = $siteManager;
        $this->formFactory = $formFactory;
    }

    /**
     * Retrieves the list of sites (paginated).
     *
     * @ApiDoc(
     *  resource=true,
     *  output={"class"="Sonata\DatagridBundle\Pager\PagerInterface", "groups"="sonata_api_read"}
     * )
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page for site list pagination")
     * @QueryParam(name="count", requirements="\d+", default="10", description="Maximum number of sites per page")
     * @QueryParam(name="enabled", requirements="0|1", nullable=true, strict=true, description="Enabled/Disabled sites filter")
     * @QueryParam(name="is_default", requirements="0|1", nullable=true, strict=true, description="Default sites filter")
     * @QueryParam(name="orderBy", requirements="ASC|DESC", nullable=true, strict=true, description="Order by array (key is field, value is direction)")
     *
     * @View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return PagerInterface
     */
    public function getSitesAction(ParamFetcherInterface $paramFetcher)
    {
        $this->setMapForOrderByParam($paramFetcher);

        $supportedCriteria = [
            'enabled' => '',
            'is_default' => '',
        ];

        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('count');
        $sort = $paramFetcher->get('orderBy');
        $criteria = array_intersect_key($paramFetcher->all(), $supportedCriteria);

        foreach ($criteria as $key => $value) {
            if (null === $value) {
                unset($criteria[$key]);
            }
        }

        if (!$sort) {
            $sort = [];
        } elseif (!is_array($sort)) {
            $sort = [$sort => 'asc'];
        }

        $pager = $this->siteManager->getPager($criteria, $page, $limit, $sort);

        return $pager;
    }

    /**
     * Retrieves a specific site.
     *
     * @ApiDoc(
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="site id"}
     *  },
     *  output={"class"="Sonata\PageBundle\Model\SiteInterface", "groups"="sonata_api_read"},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when page is not found"
     *  }
     * )
     *
     * @View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     *
     * @return SiteInterface
     */
    public function getSiteAction($id)
    {
        return $this->getSite($id);
    }

    /**
     * Adds a site.
     *
     * @ApiDoc(
     *  input={"class"="sonata_page_api_form_site", "name"="", "groups"={"sonata_api_write"}},
     *  output={"class"="Sonata\PageBundle\Model\Site", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while site creation"
     *  }
     * )
     *
     * @param Request $request A Symfony request
     *
     * @return SiteInterface
     *
     * @throws NotFoundHttpException
     */
    public function postSiteAction(Request $request)
    {
        return $this->handleWriteSite($request);
    }

    /**
     * Updates a site.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="site id"},
     *  },
     *  input={"class"="sonata_page_api_form_site", "name"="", "groups"={"sonata_api_write"}},
     *  output={"class"="Sonata\PageBundle\Model\Site", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while updating the site",
     *      404="Returned when unable to find the site"
     *  }
     * )
     *
     * @param int     $id      A Site identifier
     * @param Request $request A Symfony request
     *
     * @return SiteInterface
     *
     * @throws NotFoundHttpException
     */
    public function putSiteAction($id, Request $request)
    {
        return $this->handleWriteSite($request, $id);
    }

    /**
     * Deletes a site.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="site id"}
     *  },
     *  statusCodes={
     *      200="Returned when site is successfully deleted",
     *      400="Returned when an error has occurred while deleting the site",
     *      404="Returned when unable to find the site"
     *  }
     * )
     *
     * @param int $id A Site identifier
     *
     * @return \FOS\RestBundle\View\View
     *
     * @throws NotFoundHttpException
     */
    public function deleteSiteAction($id)
    {
        $site = $this->getSite($id);

        $this->siteManager->delete($site);

        return ['deleted' => true];
    }

    /**
     * Retrieves Site with id $id or throws an exception if it doesn't exist.
     *
     * @param $id
     *
     * @return SiteInterface
     *
     * @throws NotFoundHttpException
     */
    protected function getSite($id)
    {
        $site = $this->siteManager->findOneBy(['id' => $id]);

        if (null === $site) {
            throw new NotFoundHttpException(sprintf('Site (%d) not found', $id));
        }

        return $site;
    }

    /**
     * Write a site, this method is used by both POST and PUT action methods.
     *
     * @param Request  $request Symfony request
     * @param int|null $id      A post identifier
     *
     * @return FormInterface|FOSRestView
     */
    protected function handleWriteSite($request, $id = null)
    {
        $site = $id ? $this->getSite($id) : null;

        $form = $this->formFactory->createNamed(null, 'sonata_page_api_form_site', $site, [
            'csrf_protection' => false,
        ]);

        $form->submit($request);

        if ($form->isValid()) {
            $site = $form->getData();

            $this->siteManager->save($site);

            return $this->serializeContext($site, ['sonata_api_read']);
        }

        return $form;
    }
}
