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

use Sonata\PageBundle\Page\Manager;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\DependencyInjection\Container;



class BaseTestBlockService extends \PHPUnit_Framework_TestCase
{

    public function getService()
    {
        $container = new Container;


    }
}