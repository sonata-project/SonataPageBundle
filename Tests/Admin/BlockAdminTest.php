<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Admin;

use Sonata\PageBundle\Admin\BlockAdmin;
use Sonata\PageBundle\Tests\Model\Block;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Artur Vesker <arturvesker@gmail.com>
 */
class BlockAdminTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPersistentParametersWithPersistedSubject()
    {
        $admin = new BlockAdmin('sonata.page.admin.page', Block::class, 'SonataPageBundle:BlockAdmin');
        $subject = new Block();
        $subject->setId(1);
        $admin->setSubject($subject);
        $admin->setRequest(Request::create('/?type=foobar'));
        $this->assertSame(array('type' => null), $admin->getPersistentParameters());
    }

    public function testGetPersistentParametersWithNewSubject()
    {
        $admin = new BlockAdmin('sonata.page.admin.page', Block::class, 'SonataPageBundle:BlockAdmin');
        $subject = new Block();
        $admin->setSubject($subject);
        $admin->setRequest(Request::create('/?type=foobar'));
        $this->assertSame(array('type' => 'foobar'), $admin->getPersistentParameters());
    }
}
