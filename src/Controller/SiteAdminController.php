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
use Sonata\PageBundle\Admin\SnapshotAdmin;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Service\CreateSnapshotService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @extends CRUDController<SiteInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SiteAdminController extends CRUDController
{
    public static function getSubscribedServices(): array
    {
        return [
            'sonata.page.admin.snapshot' => SnapshotAdmin::class,
            'sonata.page.service.create_snapshot' => CreateSnapshotService::class,
        ] + parent::getSubscribedServices();
    }

    /**
     * @throws NotFoundHttpException
     * @throws AccessDeniedException
     */
    public function snapshotsAction(Request $request): Response
    {
        if (false === $this->container->get('sonata.page.admin.snapshot')->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $id = $request->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (null === $object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->admin->setSubject($object);

        if ('POST' === $request->getMethod()) {
            $this->container->get('sonata.page.service.create_snapshot')->createBySite($object);

            $this->addFlash(
                'sonata_flash_success',
                $this->admin->getTranslator()->trans('flash_snapshots_created_success', [], 'SonataPageBundle')
            );

            return new RedirectResponse($this->admin->generateUrl('edit', ['id' => $object->getId()]));
        }

        return $this->renderWithExtraParams('@SonataPage/SiteAdmin/create_snapshots.html.twig', [
            'action' => 'snapshots',
            'object' => $object,
        ]);
    }
}
