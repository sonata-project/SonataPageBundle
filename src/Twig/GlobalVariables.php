<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Twig;

use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Page\TemplateManagerInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class GlobalVariables
{
    private SiteManagerInterface $siteManager;

    private CmsManagerSelectorInterface $cmsManagerSelector;

    private SiteSelectorInterface $siteSelector;

    private TemplateManagerInterface $templateManager;

    /**
     * @var array{
     *   javascript: array<string>,
     *   stylesheet: array<string>
     * }
     */
    private array $assets;

    /**
     * @param array{
     *   javascript: array<string>,
     *   stylesheet: array<string>
     * } $assets
     */
    public function __construct(
        SiteManagerInterface $siteManager,
        CmsManagerSelectorInterface $cmsManagerSelector,
        SiteSelectorInterface $siteSelector,
        TemplateManagerInterface $templateManager,
        array $assets
    ) {
        $this->siteManager = $siteManager;
        $this->cmsManagerSelector = $cmsManagerSelector;
        $this->siteSelector = $siteSelector;
        $this->templateManager = $templateManager;
        $this->assets = $assets;
    }

    /**
     * @return array<SiteInterface>
     */
    public function getSiteAvailables(): array
    {
        return $this->siteManager->findBy([
            'enabled' => true,
        ]);
    }

    public function getCmsManager(): CmsManagerInterface
    {
        return $this->cmsManagerSelector->retrieve();
    }

    public function getCurrentSite(): ?SiteInterface
    {
        return $this->siteSelector->retrieve();
    }

    public function isEditor(): bool
    {
        return $this->cmsManagerSelector->isEditor();
    }

    public function getDefaultTemplate(): string
    {
        $template = $this->templateManager->get(
            $this->templateManager->getDefaultTemplateCode()
        );

        if (null === $template) {
            throw new \RuntimeException('Unable to find the default template');
        }

        return $template->getPath();
    }

    /**
     * @return array{
     *   javascript: array<string>,
     *   stylesheet: array<string>
     * }
     */
    public function getAssets(): array
    {
        return $this->assets;
    }
}
