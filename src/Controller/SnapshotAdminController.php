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
use Sonata\PageBundle\Form\Type\CreateSnapshotType;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Service\CreateSnapshotService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @extends CRUDController<SnapshotInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SnapshotAdminController extends CRUDController
{
    public static function getSubscribedServices(): array
    {
        return [
            'sonata.page.manager.page' => PageManagerInterface::class,
            'sonata.page.manager.snapshot' => SnapshotManagerInterface::class,
            'sonata.page.service.create_snapshot' => CreateSnapshotService::class,
        ] + parent::getSubscribedServices();
    }

    public function createAction(Request $request): Response
    {
        $this->admin->checkAccess('create');

        $class = $this->container->get('sonata.page.manager.snapshot')->getClass();

        $pageManager = $this->container->get('sonata.page.manager.page');

        $snapshot = new $class();

        if ('GET' === $request->getMethod() && $request->get('pageId')) {
            $page = $pageManager->find($request->get('pageId'));
        } elseif ($this->admin->isChild()) {
            $page = $this->admin->getParent()->getSubject();
        } else {
            $page = null; // no page selected ...
        }

        $snapshot->setPage($page);

        $form = $this->createForm(CreateSnapshotType::class, $snapshot);

        if ('POST' === $request->getMethod()) {
            $form->submit($request->request->get($form->getName()));

            if ($form->isValid()) {
                //NEXT_MAJOR: when you're going to inject this service use CreateSnapshotByPageInterface
                $createSnapshot = $this->container->get('sonata.page.service.create_snapshot');
                $snapshot = $createSnapshot->createByPage($page);

                $this->admin->create($snapshot);
            }

            return $this->redirect($this->admin->generateUrl('edit', [
                'id' => $snapshot->getId(),
            ]));
        }

        return $this->renderWithExtraParams('@SonataPage/SnapshotAdmin/create.html.twig', [
            'action' => 'create',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws AccessDeniedException
     */
    public function batchActionToggleEnabled($query): Response
    {
        $this->admin->checkAccess('batchToggleEnabled');

        $snapshotManager = $this->container->get('sonata.page.manager.snapshot');
        foreach ($query->getQuery()->iterate() as $snapshot) {
            $snapshot[0]->setEnabled(!$snapshot[0]->getEnabled());
            $snapshotManager->save($snapshot[0]);
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
