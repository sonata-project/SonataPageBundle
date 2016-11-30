<?php
namespace Sonata\PageBundle\Request;
use Sonata\PageBundle\Model\SiteInterface;


/**
 * SiteRequestContext.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface SiteRequestContextInterface
{
    /**
     * {@inheritdoc}
     */
    public function getHost();

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl();

    public function setSite(SiteInterface $site);

    public function getSite();
}