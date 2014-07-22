<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Site Admin controller
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SiteAdminController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function snapshotsAction()
    {
        if (false === $this->get('sonata.page.admin.snapshot')->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->admin->setSubject($object);

        if ($this->get('request')->getMethod() == "POST") {

            $this->get('sonata.notification.backend')
                ->createAndPublish('sonata.page.create_snapshots', array(
                    'siteId' => $object->getId(),
                    'mode'   => 'async'
                ));

            $this->addFlash('sonata_flash_success', $this->admin->trans('flash_snapshots_created_success'));

            return new RedirectResponse($this->admin->generateUrl('edit', array('id' => $object->getId())));
        }

        return $this->render('SonataPageBundle:SiteAdmin:create_snapshots.html.twig', array(
            'action'  => 'snapshots',
            'object'  => $object
        ));
    }
}
