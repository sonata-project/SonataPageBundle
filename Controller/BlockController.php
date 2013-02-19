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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block controller
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BlockController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function emptyAction()
    {
        return new Response('Empty response');
    }
}
