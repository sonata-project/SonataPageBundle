<?php

/**
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Site;

use Sonata\PageBundle\Entity\BaseSite;

/**
 * Base test class of the locale selector services
 *
 * @author Rémi Marseille <marseille@ekino.com>
 */
abstract class BaseLocaleSiteSelectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Sonata\PageBundle\Site\SiteSelectorInterface
     */
    protected $siteSelector;

    /**
     * Gets fixtures of sites
     *
     * @return Site[]
     */
    protected function getSites()
    {
        $sites = array();

        $sites[0] = new Site;
        $sites[0]->setEnabled(true);
        $sites[0]->setRelativePath('/fr');
        $sites[0]->setHost('localhost');
        $sites[0]->setIsDefault(true);
        $sites[0]->setLocale('fr');

        $sites[1] = new Site;
        $sites[1]->setEnabled(true);
        $sites[1]->setRelativePath('/en');
        $sites[1]->setHost('localhost');
        $sites[1]->setIsDefault(false);
        $sites[1]->setLocale('en');

        return $sites;
    }

    /**
     * Gets the site from site selector
     *
     * @return Site|null
     */
    protected function getSite()
    {
        return $this->siteSelector->retrieve();
    }

    /**
     * Cleanups the site selector
     */
    protected function tearDown()
    {
        unset($this->siteSelector);
    }

    /**
     * Initializes the site selector
     */
    protected function setUp()
    {
        throw new \RuntimeException('You must define a setUp method to initialize the site selector.');
    }
}

class Site extends BaseSite
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }
}
