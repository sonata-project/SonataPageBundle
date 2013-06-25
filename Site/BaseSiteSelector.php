<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Site;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;

use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\SeoBundle\Seo\SeoPageInterface;

/**
 * BaseSiteSelector
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseSiteSelector implements SiteSelectorInterface
{
    protected $siteManager;

    protected $decoratorStrategy;

    protected $seoPage;

    /**
     * @var \Sonata\PageBundle\Model\SiteInterface
     */
    protected $site;

    protected $request;

    /**
     * @param SiteManagerInterface       $siteManager
     * @param DecoratorStrategyInterface $decoratorStrategy
     * @param SeoPageInterface           $seoPage
     */
    public function __construct(SiteManagerInterface $siteManager, DecoratorStrategyInterface $decoratorStrategy, SeoPageInterface $seoPage)
    {
        $this->siteManager       = $siteManager;
        $this->decoratorStrategy = $decoratorStrategy;
        $this->seoPage           = $seoPage;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieve()
    {
        return $this->site;
    }

    /**
     * {@inheritdoc}
     */
    public function set(SiteInterface $site)
    {
        if (!$this->request) {
            return;
        }

        $this->request->getSession()->set('sonata/page/site/current', $site->getId());
    }

    /**
     * @param Request $request
     *
     * @return void
     */
    protected function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    protected function getSites()
    {
        if (!$this->request) {
            throw new \RuntimeException('No Request attached');
        }

        $siteId = $this->request->getSession() ? $this->request->getSession()->get('sonata/page/site/current') : false;

        $sites = array();
        if ($siteId) {
            $site = $this->siteManager->findOneBy(array('id' => $siteId));

            if ($site) {
                $sites = array($site);
            }
        }

        if (count($sites) == 0) {
            $sites = $this->siteManager->findBy(array(
                'host'    => array($this->request->getHost(), 'localhost'),
                'enabled' => true,
            ));
        }

        return $sites;
    }

    /**
     * {@inheritdoc}
     */
    public function onKernelRequestRedirect(GetResponseEvent $event)
    {

    }

    /**
     * {@inheritdoc}
     */
    final public function onKernelRequest(GetResponseEvent $event)
    {
        $this->setRequest($event->getRequest());

        $this->handleKernelRequest($event);
        
        if (!$this->decoratorStrategy->isRouteUriDecorable($event->getRequest()->getPathInfo())) {
            return;
        }

        if ($this->site) {
            if ($this->site->getTitle()) {
                $this->seoPage->setTitle($this->site->getTitle());
            }

            if ($this->site->getMetaDescription()) {
                $this->seoPage->addMeta('name', 'description', $this->site->getMetaDescription());
            }

            if ($this->site->getMetaKeywords()) {
                $this->seoPage->addMeta('name', 'keywords', $this->site->getMetaKeywords());
            }
        }
    }

    /**
     * @abstract
     *
     * @param GetResponseEvent $event
     *
     * @return void
     */
    abstract protected function handleKernelRequest(GetResponseEvent $event);

    /**
     * {@inheritdoc}
     */
    public function getRequestContext()
    {
        return new RequestContext();
    }
}
