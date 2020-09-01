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
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Model\BlockManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
class BlockController extends FOSRestController
{
    /**
     * @var BlockManagerInterface
     */
    protected $blockManager;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    public function __construct(BlockManagerInterface $blockManager, FormFactoryInterface $formFactory)
    {
        $this->blockManager = $blockManager;
        $this->formFactory = $formFactory;
    }

    /**
     * Retrieves a specific block.
     *
     * @ApiDoc(
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="string", "description"="Block identifier"}
     *  },
     *  output={"class"="Sonata\PageBundle\Model\BlockInterface", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when page is not found"
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param string $id Block identifier
     *
     * @return BlockInterface
     */
    public function getBlockAction($id)
    {
        return $this->getBlock($id);
    }

    /**
     * Updates a block.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="string", "description"="Block identifier"},
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
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param string  $id      Block identifier
     * @param Request $request Symfony request
     *
     * @throws NotFoundHttpException
     *
     * @return BlockInterface
     */
    public function putBlockAction($id, Request $request)
    {
        $block = $id ? $this->getBlock($id) : null;

        $form = $this->formFactory->createNamed(null, 'sonata_page_api_form_block', $block, [
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $block = $form->getData();

            $this->blockManager->save($block);

            return $block;
        }

        return $form;
    }

    /**
     * Deletes a block.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="string", "description"="Block identifier"}
     *  },
     *  statusCodes={
     *      200="Returned when block is successfully deleted",
     *      400="Returned when an error has occurred while block deletion",
     *      404="Returned when unable to find block"
     *  }
     * )
     *
     * @param string $id Block identifier
     *
     * @throws NotFoundHttpException
     *
     * @return View
     */
    public function deleteBlockAction($id)
    {
        $block = $this->getBlock($id);

        $this->blockManager->delete($block);

        return ['deleted' => true];
    }

    /**
     * Retrieves Block with id $id or throws an exception if it doesn't exist.
     *
     * @param string $id
     *
     * @throws NotFoundHttpException
     *
     * @return BlockInterface
     */
    protected function getBlock($id)
    {
        $block = $this->blockManager->findOneBy(['id' => $id]);

        if (null === $block) {
            throw new NotFoundHttpException(sprintf('Block (%d) not found', $id));
        }

        return $block;
    }
}
