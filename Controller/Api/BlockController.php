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
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sonata\BlockBundle\Model\BlockManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactoryInterface;
use FOS\RestBundle\View\View as FOSRestView;
use Sonata\BlockBundle\Model\BlockInterface;

/**
 * Class BlockController
 *
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
class BlockController
{
    /**
     * @var BlockManagerInterface
     */
    protected $blockManager;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * Constructor
     *
     * @param BlockManagerInterface $blockManager
     * @param FormFactoryInterface  $formFactory
     */
    public function __construct(BlockManagerInterface $blockManager, FormFactoryInterface $formFactory)
    {
        $this->blockManager = $blockManager;
        $this->formFactory  = $formFactory;
    }

    /**
     * Retrieves a specific block
     *
     * @ApiDoc(
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="block id"}
     *  },
     *  output={"class"="Sonata\PageBundle\Model\BlockInterface", "groups"="sonata_api_read"},
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
     * @return BlockInterface
     */
    public function getBlockAction($id)
    {
        return $this->getBlock($id);
    }

    /**
     * Updates a block
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="block identifier"},
     *  },
     *  input={"class"="sonata_page_api_form_block", "name"="", "groups"={"sonata_api_write"}},
     *  output={"class"="Sonata\PageBundle\Model\Block", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while block creation",
     *      404="Returned when unable to find page"
     *  }
     * )
     *
     * @param int     $id      A Block identifier
     * @param Request $request A Symfony request
     *
     * @return BlockInterface
     *
     * @throws NotFoundHttpException
     */
    public function putBlockAction($id, Request $request)
    {
        $block = $id ? $this->getBlock($id) : null;

        $form = $this->formFactory->createNamed(null, 'sonata_page_api_form_block', $block, array(
            'csrf_protection' => false
        ));

        $form->bind($request);

        if ($form->isValid()) {
            $block = $form->getData();

            $this->blockManager->save($block);

            $view = FOSRestView::create($block);
            $serializationContext = SerializationContext::create();
            $serializationContext->setGroups(array('sonata_api_read'));
            $serializationContext->enableMaxDepthChecks();
            $view->setSerializationContext($serializationContext);

            return $view;
        }

        return $form;
    }

    /**
     * Deletes a block
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="block identifier"}
     *  },
     *  statusCodes={
     *      200="Returned when block is successfully deleted",
     *      400="Returned when an error has occured while block deletion",
     *      404="Returned when unable to find block"
     *  }
     * )
     *
     * @param integer $id A Block identifier
     *
     * @return \FOS\RestBundle\View\View
     *
     * @throws NotFoundHttpException
     */
    public function deleteBlockAction($id)
    {
        $block = $this->getBlock($id);

        $this->blockManager->delete($block);

        return array('deleted' => true);
    }

    /**
     * Retrieves Block with id $id or throws an exception if it doesn't exist
     *
     * @param $id
     *
     * @return BlockInterface
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getBlock($id)
    {
        $block = $this->blockManager->findOneBy(array('id' => $id));

        if (null === $block) {
            throw new NotFoundHttpException(sprintf('Block (%d) not found', $id));
        }

        return $block;
    }
}
