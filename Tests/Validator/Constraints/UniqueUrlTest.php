<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Validator\Constraints;

use Sonata\PageBundle\Validator\Constraints\UniqueUrl;

class UniqueUrlTest extends \PHPUnit_Framework_TestCase
{

    public function testInstance()
    {
        $constraint = new UniqueUrl();

        $this->assertEquals('class', $constraint->getTargets());
        $this->assertEquals('sonata.page.validator.unique_url', $constraint->validatedBy());
    }
}
