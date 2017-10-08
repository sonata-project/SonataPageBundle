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
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
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

    /**
     * @param SnapshotManagerInterface $snapshotManager
     */
    public function __construct(SnapshotManagerInterface $snapshotManager)
    {
        $this->snapshotManager = $snapshotManager;
    }

    /**
     * Retrieves the list of snapshots (paginated).
     *
     * @ApiDoc(
     *  resource=true,
     *  output={"class"="Sonata\DatagridBundle\Pager\PagerInterface", "groups"="sonata_api_read"}
     * )
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page for snapshots list pagination")
     * @QueryParam(name="count", requirements="\d+", default="10", description="Maximum number of snapshots per page")
     * @QueryParam(name="site", requirements="\d+", nullable=true, strict=true, description="Filter snapshots for a specific site's id")
     * @QueryParam(name="page_id", requirements="\d+", nullable=true, strict=true, description="Filter snapshots for a specific page's id")
     * @QueryParam(name="root", requirements="0|1", nullable=true, strict=true, description="Filter snapshots having no parent id")
     * @QueryParam(name="parent", requirements="\d+", nullable=true, strict=true, description="Get snapshots being child of given snapshots id")
     * @QueryParam(name="enabled", requirements="0|1", nullable=true, strict=true, description="Enabled/Disabled snapshots filter")
     * @QueryParam(name="orderBy", requirements="ASC|DESC", nullable=true, strict=true, description="Order by array (key is field, value is direction)")
     *
     * @View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return PagerInterface
     */
    public function getSnapshotsAction(ParamFetcherInterface $paramFetcher)
    {
        $this->setMapForOrderByParam($paramFetcher);

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

        $pager = $this->snapshotManager->getPager($criteria, $page, $limit, $sort);

        return $pager;
    }

    /**
     * Retrieves a specific snapshot.
     *
     * @ApiDoc(
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="snapshot id"}
     *  },
     *  output={"class"="Sonata\PageBundle\Model\SnapshotInterface", "groups"="sonata_api_read"},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when snapshots is not found"
     *  }
     * )
     *
     * @View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param $id
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
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="snapshot id"}
     *  },
     *  statusCodes={
     *      200="Returned when snapshots is successfully deleted",
     *      400="Returned when an error has occurred while deleting the snapshots",
     *      404="Returned when unable to find the snapshots"
     *  }
     * )
     *
     * @param int $id A Snapshot identifier
     *
     * @return \FOS\RestBundle\View\View
     *
     * @throws NotFoundHttpException
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
     * @param $id
     *
     * @return SnapshotInterface
     *
     * @throws NotFoundHttpException
     */
    protected function getSnapshot($id)
    {
        $snapshot = $this->snapshotManager->findOneBy(['id' => $id]);

        if (null === $snapshot) {
            throw new NotFoundHttpException(sprintf('Snapshot (%d) not found', $id));
        }

        return $snapshot;
    }
}
