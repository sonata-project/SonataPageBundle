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

class BlockAdminController extends Controller
{
    public function savePositionAction()
    {
        // todo : add security check
        $params = $this->get('request')->get('disposition');

        $result = $this->get('sonata.page.manager')->savePosition($params);

        return $this->renderJson(array('result' => $result ? 'ok' : 'ko'));
    }
}