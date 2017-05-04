<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\DependencyInjection;

use Sonata\PageBundle\DependencyInjection\SonataPageExtension;

/**
 * Tests the SonataPageExtension.
 *
 * @author Rémi Marseille <marseille@ekino.com>
 */
class SonataPageExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the configureClassesToCompile method.
     */
    public function testConfigureClassesToCompile()
    {
        $extension = new SonataPageExtension();
        $extension->configureClassesToCompile();

        $this->assertNotContains(
            'Sonata\\PageBundle\\Request\\SiteRequest',
            $extension->getClassesToCompile()
        );
        $this->assertNotContains(
            'Sonata\\PageBundle\\Request\\SiteRequestInterface',
            $extension->getClassesToCompile()
        );
    }
}
