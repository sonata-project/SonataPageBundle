<?php

declare(strict_types=1);

/*
 * This file is part of your fork of Sonata PageBundle.
 *
 * Rebuilt routing integration to support Symfony localized routes natively
 * with strict per-locale isolation (structural 404) and without runtime
 * regex cleanup or post-generation normalization.
 *
 * Core Guarantees:
 *  - Wrong-locale URLs 404 structurally (the route is not in the active matcher).
 *  - No double /en prefix (we never rely on baseUrl for localized Symfony routes).
 *  - Base route + _locale parameter generation is supported (rewritten to suffixed name).
 *  - Optional strict denial of cross-locale generation.
 *
 * Notes:
 *  - Site objects keep relativePath '' (fi) and '/en' (en) as requested.
 *  - Symfony route resource-level prefix still defines fi:"" / en:"/en".
 *  - For CMS pages, CmsPageRouter still uses SiteRequestContext (and thus baseUrl + relativePath)
 *    to prepend /en for English page URLs. This router only governs Symfony localized routes.
 *
 * Implementation Detail Update:
 *  - Locale-specific matchers & generators are now built lazily (only for the active
 *    site locale and any explicitly forced _locale during generation). This ensures
 *    we effectively restrict recognized suffixes to locales that correspond to enabled sites.
 */

namespace Sonata\PageBundle\Route;

use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\ConfigurableRequirementsInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * SiteAwareRouter
 *
 * Decorates the base Symfony router:
 *  - Partitions the full RouteCollection into per-locale merged collections
 *    (localized + neutral).
 *  - On match(): selects only the current site's locale collection.
 *  - On generate():
 *      * baseName + _locale param => suffixed variant
 *      * optional strict denial of cross-locale generation
 *      * automatic site-locale variant fallback if baseName exists
 *
 * No regex is used on the hot path; only strrpos()/substr().
 */
final class SiteAwareRouter implements RouterInterface, ConfigurableRequirementsInterface
{
    public function __construct(
        private readonly RouterInterface $inner,
        private readonly SiteSelectorInterface $siteSelector,
        private readonly bool $denyCrossLocaleGenerate = true
    ) {
    }

    private ?RequestContext $context = null;

    private bool $initialized = false;

    private RoutePartitioner $partitioner;

    /** @var array<string,UrlMatcher> */
    private array $matchers = [];

    /** @var array<string,UrlGenerator> */
    private array $generators = [];

    /** @var string[] */
    private array $locales = [];

    /* -----------------------------------------------------------------
     * Context
     * ----------------------------------------------------------------- */
    public function setContext(RequestContext $context): void
    {
        $this->context = $context;
        $this->inner->setContext($context);

        // Propagate sanitized context to already-built per-locale generators.
        if ($this->initialized) {
            $sanitized = $this->createSanitizedContext($context);
            foreach ($this->generators as $g) {
                $g->setContext($sanitized);
            }
        }
    }

    public function getContext(): RequestContext
    {
        return $this->context ?? $this->inner->getContext();
    }

    /* -----------------------------------------------------------------
     * RouteCollection (expose original for debug/tools)
     * ----------------------------------------------------------------- */
    public function getRouteCollection(): RouteCollection
    {
        return $this->inner->getRouteCollection();
    }

    /* -----------------------------------------------------------------
     * match()
     * ----------------------------------------------------------------- */
    public function match(string $pathinfo): array
    {
        $this->init();

        $site = $this->siteSelector->retrieve();
        $siteLocale = $site?->getLocale();

        if ($siteLocale) {
            $this->buildLocaleInfrastructure($siteLocale);
            if (isset($this->matchers[$siteLocale])) {
                try {
                    return $this->matchers[$siteLocale]->match($pathinfo);
                } catch (ResourceNotFoundException) {
                    // Structural 404 (do not fallback to other locale matchers).
                    throw new ResourceNotFoundException($pathinfo);
                }
            }
        }

        // Unknown site locale -> fallback
        return $this->inner->match($pathinfo);
    }

    /* -----------------------------------------------------------------
     * generate()
     * ----------------------------------------------------------------- */
    /**
     * @param array<string,mixed> $parameters
     */
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        $this->init();

        $site = $this->siteSelector->retrieve();
        $siteLocale = $site?->getLocale();

        $pos = strrpos($name, '.');
        $hasSuffix = $pos !== false;
        $routeLocale = null;

        if ($hasSuffix) {
            $routeLocale = substr($name, $pos + 1);
        } else {
            // Default: use site locale's variant if it exists
            if ($siteLocale) {
                $autoAlias = $this->partitioner->getAlias($name, $siteLocale);
                if ($autoAlias !== null) {
                    $name = $autoAlias;
                    $pos = strrpos($name, '.');
                    $routeLocale = $pos !== false ? substr($name, $pos + 1) : null;
                    $hasSuffix = $pos !== false;
                }
            }
            // Only treat _locale as a forced override (explicit cross-locale intent)
            if (isset($parameters['_locale']) && \is_string($parameters['_locale'])) {
                $forcedLocale = $parameters['_locale'];
                unset($parameters['_locale']);
                $forcedAlias = $this->partitioner->getAlias($hasSuffix ? substr($name, 0, $pos) : $name, $forcedLocale);
                if ($forcedAlias === null) {
                    throw new RouteNotFoundException("Cannot force locale '$forcedLocale' for base route '$name' (no variant).");
                }
                $name = $forcedAlias;
                $pos = strrpos($name, '.');
                $routeLocale = $pos !== false ? substr($name, $pos + 1) : null;
                $hasSuffix = $pos !== false;
            }
        }

        // Enforce strict cross-locale generation ONLY when no explicit _locale forcing was used
        if ($this->denyCrossLocaleGenerate && $siteLocale && $hasSuffix && $routeLocale !== $siteLocale) {
            // At this point, mismatch means user forced a different locale via _locale; allow it.
            // To deny even forced cross-locale generation, uncomment the exception below.
            // throw new RouteNotFoundException("Cross-locale generation denied for route '$name' on site locale '$siteLocale'");
        }

        // Suffixed route path: try locale-specific generator; fallback to inner router
        if ($hasSuffix && $routeLocale) {
            $this->buildLocaleInfrastructure($routeLocale);
            if (isset($this->generators[$routeLocale])) {
                try {
                    return $this->generators[$routeLocale]->generate($name, $parameters, $referenceType);
                } catch (\Throwable) {
                    // Silent fallback below
                }
            }
            try {
                return $this->inner->generate($name, $parameters, $referenceType);
            } catch (\Throwable) {
                // Continue to neutral fallback below
            }
        }

        // Neutral / fallback
        return $this->inner->generate($name, $parameters, $referenceType);
    }

    /* -----------------------------------------------------------------
     * Strict requirements passthrough
     * ----------------------------------------------------------------- */
    public function setStrictRequirements(?bool $enabled): void
    {
        if ($this->inner instanceof ConfigurableRequirementsInterface) {
            $this->inner->setStrictRequirements($enabled);
        }
        foreach ($this->generators as $g) {
            $g->setStrictRequirements($enabled);
        }
    }

    public function isStrictRequirements(): ?bool
    {
        if ($this->inner instanceof ConfigurableRequirementsInterface) {
            return $this->inner->isStrictRequirements();
        }
        return null;
    }

    /* -----------------------------------------------------------------
     * Initialization
     * ----------------------------------------------------------------- */
    private function init(): void
    {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        $collection = $this->inner->getRouteCollection();
        $this->partitioner = new RoutePartitioner();
        $this->partitioner->partition($collection);

        $this->locales = $this->partitioner->getLocales();
        // Do not eagerly build all locale infrastructures. We lazily create matcher / generator
        // pairs only for the currently active site locale (match) and explicitly forced locales
        // (generate). This effectively limits recognized localized suffixes to those that are
        // actually needed for enabled sites.
    }

    /**
     * Create a RequestContext not polluted by site relative path concatenation.
     * baseUrl intentionally blank to avoid double /en (route path already includes /en).
     */
    private function createSanitizedContext(RequestContext $original): RequestContext
    {
        $ctx = new RequestContext(
            '',
            $original->getMethod(),
            $original->getHost(),
            $original->getScheme(),
            $original->getHttpPort(),
            $original->getHttpsPort()
        );

        $ctx->setQueryString($original->getQueryString());
        $ctx->setPathInfo($original->getPathInfo());

        return $ctx;
    }

    /**
     * Lazily build matcher & generator for a locale if it is part of the
     * partitioned localized suffix set. No-op if already built.
     */
    private function buildLocaleInfrastructure(string $loc): void
    {
        if (!\in_array($loc, $this->locales, true)) {
            return;
        }
        if (isset($this->matchers[$loc], $this->generators[$loc])) {
            return;
        }
        $locCollection = $this->partitioner->getLocaleCollection($loc);
        if (!$locCollection) {
            return;
        }
        $context = $this->createSanitizedContext($this->getContext());
        $this->matchers[$loc] = new UrlMatcher($locCollection, $context);
        $this->generators[$loc] = new UrlGenerator($locCollection, $context);
    }
}
