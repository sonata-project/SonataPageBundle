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
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Benoit de Jacobet <benoit.de-jacobet@ekino.com>
 */
class SnapshotController extends FOSRestController
{
    /**
     * @var SnapshotManagerInterface
     */
    protected $snapshotManager;

    public function __construct(SnapshotManagerInterface $snapshotManager)
    {
        $this->snapshotManager = $snapshotManager;
    }

    /**
     * Retrieves the list of snapshots (paginated).
     *
     * @Operation(
     *     tags={"/api/page/snapshots"},
     *     summary="Retrieves the list of snapshots (paginated).",
     *     @SWG\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page for snapshots list pagination",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="count",
     *         in="query",
     *         description="Maximum number of snapshots per page",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="site",
     *         in="query",
     *         description="Filter snapshots for a specific site's id",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="page_id",
     *         in="query",
     *         description="Filter snapshots for a specific page's id",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="root",
     *         in="query",
     *         description="Filter snapshots having no parent id",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="parent",
     *         in="query",
     *         description="Get snapshots being child of given snapshots id",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="enabled",
     *         in="query",
     *         description="Enables or disables the snapshots filter",
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
     *         @SWG\Schema(ref=@Model(type="Sonata\DatagridBundle\Pager\PagerInterface"))
     *     )
     * )
     *
     * @Rest\QueryParam(name="page", requirements="\d+", default="1", description="Page for snapshots list pagination")
     * @Rest\QueryParam(name="count", requirements="\d+", default="10", description="Maximum number of snapshots per page")
     * @Rest\QueryParam(name="site", requirements="\d+", nullable=true, strict=true, description="Filter snapshots for a specific site's id")
     * @Rest\QueryParam(name="page_id", requirements="\d+", nullable=true, strict=true, description="Filter snapshots for a specific page's id")
     * @Rest\QueryParam(name="root", requirements="0|1", nullable=true, strict=true, description="Filter snapshots having no parent id")
     * @Rest\QueryParam(name="parent", requirements="\d+", nullable=true, strict=true, description="Get snapshots being child of given snapshots id")
     * @Rest\QueryParam(name="enabled", requirements="0|1", nullable=true, strict=true, description="Enables or disables the snapshots filter")
     * @Rest\QueryParam(name="orderBy", map=true, requirements="ASC|DESC", nullable=true, strict=true, description="Order by array (key is field, value is direction)")
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @return PagerInterface
     */
    public function getSnapshotsAction(ParamFetcherInterface $paramFetcher)
    {
        $supportedCriteria = [
            'enabled' => '',
            'site' => '',
            'page_id' => '',
            'root' => '',
            'parent' => '',
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

        return $this->snapshotManager->getPager($criteria, $page, $limit, $sort);
    }

    /**
     * Retrieves a specific snapshot.
     *
     * @Operation(
     *     tags={"/api/page/snapshots"},
     *     summary="Retrieves a specific snapshot.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\PageBundle\Model\SnapshotInterface"))
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when snapshots is not found"
     *     )
     * )
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param string $id Snapshot identifier
     *
     * @return SnapshotInterface
     */
    public function getSnapshotAction($id)
    {
        return $this->getSnapshot($id);
    }

    /**
     * Deletes a snapshot.
     *
     * @Operation(
     *     tags={"/api/page/snapshots"},
     *     summary="Deletes a snapshot.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when snapshots is successfully deleted"
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when an error has occurred while deleting the snapshots"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when unable to find the snapshots"
     *     )
     * )
     *
     * @param string $id Snapshot identifier
     *
     * @throws NotFoundHttpException
     *
     * @return View
     */
    public function deleteSnapshotAction($id)
    {
        $snapshots = $this->getSnapshot($id);

        $this->snapshotManager->delete($snapshots);

        return ['deleted' => true];
    }

    /**
     * Retrieves Snapshot with id $id or throws an exception if it doesn't exist.
     *
     * @param string $id Snapshot identifier
     *
     * @throws NotFoundHttpException
     *
     * @return SnapshotInterface
     */
    protected function getSnapshot($id)
    {
        $snapshot = $this->snapshotManager->findOneBy(['id' => $id]);

        if (null === $snapshot) {
            throw new NotFoundHttpException(sprintf('Snapshot not found for identifier %s.', var_export($id, true)));
        }

        return $snapshot;
    }
}
