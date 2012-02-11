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

class BlockAdminController extends Controller
{
    public function savePositionAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_SONATA_PAGE_ADMIN_BLOCK_EDIT')) {
            throw new AccessDeniedException();
        }

        $params = $this->get('request')->get('disposition');

        $result = $this->get('sonata.page.block_interactor')->saveBlocksPosition($params);

        return $this->renderJson(array('result' => $result ? 'ok' : 'ko'));
    }

    public function createAction()
    {

        return $this->render('SonataPageBundle:BlockAdmin:create.html.twig', array(
            'action' => 'create'
        ));
    }
}