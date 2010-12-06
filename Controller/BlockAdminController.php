<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlockAdminController extends Controller
{

    public function returnJson($data) {
        $response = new \Symfony\Component\HttpFoundation\Response;
        $response->setContent(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }
    public function savePositionAction()
    {
        // todo : add security check
        $params = $this->get('request')->get('disposition');

        $result = $this->container->get('page.manager')->savePosition($params);

        return $this->returnJson(array('result' => $result ? 'ok' : 'ko'));
    }

    public function updateAction()
    {

    }

    public function viewAction($id)
    {

    }

    public function createAction()
    {

    }
}