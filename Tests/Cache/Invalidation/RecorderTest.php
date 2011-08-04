<?php


/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Cache\Invalidation;

use Sonata\PageBundle\Cache\Invalidation\ModelCollectionIdentifiers;
use Sonata\PageBundle\Cache\Invalidation\Recorder;

class Recorder_Model_1
{
    public function getCacheIdentifier()
    {
        return 1;
    }
}

class Recorder_Model_2
{

    public function getId()
    {
        return 2;
    }
}

class RecorderTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $collection = new ModelCollectionIdentifiers(array(
            'Sonata\PageBundle\Tests\Cache\Invalidation\Recorder_Model_1' => 'getCacheIdentifier'
        ));

        $m1 = new Recorder_Model_1();
        $m2 = new Recorder_Model_2();
        $recorder = new Recorder($collection);
        $recorder->add($m1);
        $recorder->add($m2);

        $this->assertArrayHasKey('Sonata\PageBundle\Tests\Cache\Invalidation\Recorder_Model_1', $recorder->get());
        $this->assertArrayHasKey('Sonata\PageBundle\Tests\Cache\Invalidation\Recorder_Model_2', $recorder->get());

        $this->assertEquals(array('0' => 1), $recorder->get('Sonata\PageBundle\Tests\Cache\Invalidation\Recorder_Model_1'));
        $recorder->reset();
        $this->assertEquals(array(), $recorder->get('Sonata\PageBundle\Tests\Cache\Invalidation\Recorder_Model_1'));
    }
}
