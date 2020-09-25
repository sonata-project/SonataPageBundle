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

namespace Sonata\PageBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Sonata\DatagridBundle\Pager\BasePager;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\PageBundle\Model\Site;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Swagger\Annotations as SWG;
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

    public function __construct(SiteManagerInterface $siteManager, FormFactoryInterface $formFactory)
    {
        $this->siteManager = $siteManager;
        $this->formFactory = $formFactory;
    }

    /**
     * Retrieves the list of sites (paginated).
     *
     * @Operation(
     *     tags={"/api/page/pages"},
     *     summary="Retrieves the list of sites (paginated).",
     *     @SWG\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page for site list pagination",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="count",
     *         in="query",
     *         description="Maximum number of sites per page",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="enabled",
     *         in="query",
     *         description="Enables or disables the sites filter",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="is_default",
     *         in="query",
     *         description="Default sites filter",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="orderBy",
     *         in="query",
     *         description="Order by array (key is field, value is direction)",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type=BasePager::class, groups={"sonata_api_read"}))
     *     )
     * )
     *
     * @Rest\QueryParam(name="page", requirements="\d+", default="1", description="Page for site list pagination")
     * @Rest\QueryParam(name="count", requirements="\d+", default="10", description="Maximum number of sites per page")
     * @Rest\QueryParam(name="enabled", requirements="0|1", nullable=true, strict=true, description="Enables or disables the sites filter")
     * @Rest\QueryParam(name="is_default", requirements="0|1", nullable=true, strict=true, description="Default sites filter")
     * @Rest\QueryParam(name="orderBy", map=true, requirements="ASC|DESC", nullable=true, strict=true, description="Order by array (key is field, value is direction)")
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @return PagerInterface
     */
    public function getSitesAction(ParamFetcherInterface $paramFetcher)
    {
        $supportedCriteria = [
            'enabled' => '',
            'is_default' => '',
        ];

        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('count');
        $sort = $paramFetcher->get('orderBy');
        $criteria = array_intersect_key($paramFetcher->all(), $supportedCriteria);

        $criteria = array_filter($criteria, static function ($value): bool {
            return null !== $value;
        });

        if (!$sort) {
            $sort = [];
        } elseif (!\is_array($sort)) {
            $sort = [$sort => 'asc'];
        }

        return $this->siteManager->getPager($criteria, $page, $limit, $sort);
    }

    /**
     * Retrieves a specific site.
     *
     * @Operation(
     *     tags={"/api/page/pages"},
     *     summary="Retrieves a specific site.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type=Site::class, groups={"sonata_api_read"}))
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when page is not found"
     *     )
     * )
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param string $id Site identifier
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
     * @Operation(
     *     tags={"/api/page/pages"},
     *     summary="Adds a site.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type=Site::class, groups={"sonata_api_read"}))
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when an error has occurred while site creation"
     *     )
     * )
     *
     * @throws NotFoundHttpException
     *
     * @return SiteInterface
     */
    public function postSiteAction(Request $request)
    {
        return $this->handleWriteSite($request);
    }

    /**
     * Updates a site.
     *
     * @Operation(
     *     tags={"/api/page/pages"},
     *     summary="Updates a site.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type=Site::class, groups={"sonata_api_read"}))
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when an error has occurred while updating the site"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when unable to find the site"
     *     )
     * )
     *
     * @param string  $id      Site identifier
     *
     * @throws NotFoundHttpException
     *
     * @return SiteInterface
     */
    public function putSiteAction($id, Request $request)
    {
        return $this->handleWriteSite($request, $id);
    }

    /**
     * Deletes a site.
     *
     * @Operation(
     *     tags={"/api/page/pages"},
     *     summary="Deletes a site.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when site is successfully deleted"
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when an error has occurred while deleting the site"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when unable to find the site"
     *     )
     * )
     *
     * @param string $id Site identifier
     *
     * @throws NotFoundHttpException
     *
     * @return View
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
     * @param string $id Site identifier
     *
     * @throws NotFoundHttpException
     *
     * @return SiteInterface
     */
    protected function getSite($id)
    {
        $site = $this->siteManager->findOneBy(['id' => $id]);

        if (null === $site) {
            throw new NotFoundHttpException(sprintf('Site not found for identifier %s.', var_export($id, true)));
        }

        return $site;
    }

    /**
     * Write a site, this method is used by both POST and PUT action methods.
     *
     * @param Request     $request Symfony request
     * @param string|null $id      Site identifier
     *
     * @return FormInterface|View
     */
    protected function handleWriteSite($request, $id = null)
    {
        $site = $id ? $this->getSite($id) : null;

        $form = $this->formFactory->createNamed(null, 'sonata_page_api_form_site', $site, [
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $site = $form->getData();

            $this->siteManager->save($site);

            return $this->serializeContext($site, ['sonata_api_read']);
        }

        return $form;
    }
}
