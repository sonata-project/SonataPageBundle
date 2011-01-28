<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Block;


class FakeTemplating
{
    public $view;

    public $params;

    public $response;

    public $template;

    public function render($view, $params, $response = null)
    {
        $this->view = $view;
        $this->params = $params;
        $this->response = $response;
    }

    public function renderResponse($template, $params)
    {
        $this->template = $template;
        $this->params = $params;
    }
}