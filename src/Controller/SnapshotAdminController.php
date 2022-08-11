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
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
            'sonata.page.manager.snapshot' => SnapshotManagerInterface::class,
        ] + parent::getSubscribedServices();
    }

    /**
     * @throws AccessDeniedException
     */
    public function batchActionToggleEnabled(ProxyQueryInterface $query): RedirectResponse
    {
        $this->admin->checkAccess('batchToggleEnabled');

        $snapshotManager = $this->container->get('sonata.page.manager.snapshot');
        foreach ($query->execute() as $snapshot) {
            \assert($snapshot instanceof SnapshotInterface);

            $snapshot->setEnabled(!$snapshot->getEnabled());
            $snapshotManager->save($snapshot);
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
