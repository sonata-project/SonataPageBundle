<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class SonataPageBundle_AllTests
{

    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite('Sonata - PageBundle Test suite');

        $suite->addTestFile('Page/ManagerTest.php');
        $suite->addTestFile('Block/TextBlockServiceTest.php');

        return $suite;
    }
}
