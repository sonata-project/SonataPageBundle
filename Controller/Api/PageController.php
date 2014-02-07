<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\PageBundle\Controller\Api;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;

/**
 * Class PageController
 *
 * @package Sonata\PageBundle\Controller\Api
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class PageController
{
    /**
     * @var \Sonata\PageBundle\Model\PageManagerInterface
     */
    protected $pageManager;

    /**
     * Constructor
     *
     * @param PageManagerInterface $pageManager
     */
    public function __construct(PageManagerInterface $pageManager)
    {
        $this->pageManager = $pageManager;
    }

    /**
     * Retrieves the list of pages (paginated)
     *
     * @ApiDoc(
     *  resource=true,
     *  output={"class"="Sonata\PageBundle\Model\PageInterface", "groups"="sonata_api_read"}
     * )
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page for 'page' list pagination")
     * @QueryParam(name="count", requirements="\d+", default="10", description="Number of pages by page")
     * @QueryParam(name="enabled", requirements="0|1", nullable=true, strict=true, description="Enabled/Disabled pages filter")
     * @QueryParam(name="orderBy", array=true, requirements="ASC|DESC", nullable=true, strict=true, description="Order by array (key is field, value is direction)")
     *
     * @View(serializerGroups="sonata_api_read", serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return PageInterface[]
     */
    public function getPagesAction(ParamFetcherInterface $paramFetcher)
    {
        $supportedFilters = array(
            'enabled' => "",
        );

        $page    = $paramFetcher->get('page') - 1;
        $count   = $paramFetcher->get('count');
        $orderBy = $paramFetcher->get('orderBy');
        $filters = array_intersect_key($paramFetcher->all(), $supportedFilters);

        foreach ($filters as $key => $value) {
            if (null === $value) {
                unset($filters[$key]);
            }
        }
        return $this->pageManager->findBy($filters, $orderBy, $count, $page);
    }

    /**
     * Retrieves a specific page
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="page id"}
     *  },
     *  output={"class"="Sonata\PageBundle\Model\PageInterface", "groups"="sonata_api_read"},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when page is not found"
     *  }
     * )
     *
     * @View(serializerGroups="sonata_api_read", serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     *
     * @return PageInterface
     */
    public function getPageAction($id)
    {
        return $this->getPage($id);
    }

    /**
     * Retrieves a specific page's blocks
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="page id"}
     *  },
     *  output={"class"="Sonata\PageBundle\Model\PageBlockInterface", "groups"="sonata_api_read"},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when page is not found"
     *  }
     * )
     *
     * @View(serializerGroups="sonata_api_read", serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     *
     * @return PageBlockInterface[]
     */
    public function getPagePageblocksAction($id)
    {
        return $this->getPage($id)->getBlocks();
    }

    /**
     * Retrieves page with id $id or throws an exception if it doesn't exist
     *
     * @param $id
     *
     * @return PageInterface
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getPage($id)
    {
        $page = $this->pageManager->findOneBy(array('id' => $id));

        if (null === $page) {
            throw new NotFoundHttpException(sprintf('Page (%d) not found', $id));
        }

        return $page;
    }


}