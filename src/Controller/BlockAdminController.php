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

namespace Sonata\PageBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Exception\BadRequestParamHttpException;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\PageBundle\Model\BlockInteractorInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @extends CRUDController<PageBlockInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class BlockAdminController extends CRUDController
{
    public static function getSubscribedServices(): array
    {
        return [
            'sonata.page.block_interactor' => BlockInteractorInterface::class,
            'sonata.block.manager' => BlockServiceManagerInterface::class,
        ] + parent::getSubscribedServices();
    }

    /**
     * @throws AccessDeniedException
     */
    public function savePositionAction(Request $request): Response
    {
        $this->admin->checkAccess('savePosition');

        try {
            // TODO: Change to $request->query->all('filter') when support for Symfony < 5.1 is dropped.
            /** @var array<array{id?: int|string, position?: string, parent_id?: int|string, page_id?: int|string}> $params */
            $params = $request->request->all()['disposition'] ?? [];

            if ([] === $params) {
                throw new HttpException(400, 'wrong parameters');
            }

            $blockInteractor = $this->container->get('sonata.page.block_interactor');
            \assert($blockInteractor instanceof BlockInteractorInterface);

            $result = $blockInteractor->saveBlocksPosition($params);
            $status = 200;
        } catch (HttpException $e) {
            $status = $e->getStatusCode();
            $result = [
                'exception' => \get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        } catch (\Exception $e) {
            $status = 500;
            $result = [
                'exception' => \get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }

        $result = (true === $result) ? 'ok' : $result;

        return $this->renderJson(['result' => $result], $status);
    }

    public function createAction(Request $request): Response
    {
        $this->admin->checkAccess('create');

        $parameters = $this->admin->getPersistentParameters();

        $blockManager = $this->container->get('sonata.block.manager');
        \assert($blockManager instanceof BlockServiceManagerInterface);

        if (null === $parameters['type']) {
            return $this->renderWithExtraParams('@SonataPage/BlockAdmin/select_type.html.twig', [
                'services' => $blockManager->getServicesByContext('sonata_page_bundle'),
                'base_template' => $this->getBaseTemplate(),
                'admin' => $this->admin,
                'action' => 'create',
            ]);
        }

        return parent::createAction($request);
    }

    public function switchParentAction(Request $request): Response
    {
        $blockId = $request->get('block_id');

        if (null === $blockId) {
            throw new BadRequestParamHttpException('block_id', ['int', 'string'], $blockId);
        }

        $parentId = $request->get('parent_id');

        if (null === $parentId) {
            throw new BadRequestParamHttpException('parent_id', ['int', 'string'], $parentId);
        }

        $block = $this->admin->getObject($blockId);

        if (null === $block) {
            throw new BadRequestHttpException(sprintf('Unable to find block with id: "%s"', $blockId));
        }

        $parent = $this->admin->getObject($parentId);

        if (null === $parent) {
            throw new BadRequestHttpException(sprintf('Unable to find parent block with id: "%s"', $parentId));
        }

        $this->admin->checkAccess('switchParent', $block);

        $block->setParent($parent);
        $this->admin->update($block);

        return $this->renderJson(['result' => 'ok']);
    }

    /**
     * @throws AccessDeniedException
     * @throws BadRequestHttpException
     */
    public function composePreviewAction(Request $request): Response
    {
        $existingObject = $this->assertObjectExists($request, true);
        \assert(null !== $existingObject);

        $this->checkParentChildAssociation($request, $existingObject);

        $this->admin->checkAccess('composePreview', $existingObject);

        $container = $existingObject->getParent();

        if (null === $container) {
            throw new BadRequestHttpException('No parent found, unable to preview an orphan block');
        }

        $this->admin->setSubject($existingObject);

        $blockManager = $this->container->get('sonata.block.manager');
        \assert($blockManager instanceof BlockServiceManagerInterface);
        $blockServices = $blockManager->getServicesByContext('sonata_page_bundle', false);

        return $this->renderWithExtraParams('@SonataPage/BlockAdmin/compose_preview.html.twig', [
            'container' => $container,
            'child' => $existingObject,
            'blockServices' => $blockServices,
            'blockAdmin' => $this->admin,
        ]);
    }
}
