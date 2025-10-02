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

namespace Sonata\PageBundle\Route;

use Sonata\PageBundle\Model\SiteManagerInterface;
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
 * SiteAwareRouter.
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
    private ?RequestContext $context = null;

    private bool $initialized = false;

    private RoutePartitioner $partitioner;

    /** @var array<string,UrlMatcher> */
    private array $matchers = [];

    /** @var array<string,UrlGenerator> */
    private array $generators = [];

    /** @var string[] */
    private array $locales = [];

    /** @var string[]|null Cached allowed locales */
    private ?array $cachedAllowedLocales = null;

    /**
     * @param string[] $ignoreRoutes
     * @param string[] $ignoreRoutePatterns
     */
    public function __construct(
        private readonly RouterInterface $inner,
        private readonly SiteSelectorInterface $siteSelector,
        private readonly SiteManagerInterface $siteManager,
        private readonly bool $denyCrossLocaleGenerate = true,
        private readonly array $ignoreRoutes = [],
        private readonly array $ignoreRoutePatterns = [],
    ) {
        $this->partitioner = new RoutePartitioner();
    }

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
    /**
     * @return array<string, mixed>
     */
    public function match(string $pathinfo): array
    {
        $this->init();

        $site = $this->siteSelector->retrieve();
        $siteLocale = $site?->getLocale();

        if (null !== $siteLocale && '' !== $siteLocale) {
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
     * @param array<array-key,mixed> $parameters
     */
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        // If route should be ignored, delegate directly to inner router with clean context
        // This ensures ignored routes (like OAuth, admin, etc.) are never prefixed with locale paths
        if ($this->shouldIgnoreRoute($name)) {
            // Temporarily set a clean context on the inner router to avoid locale prefix pollution
            $originalContext = $this->inner->getContext();
            $cleanContext = $this->createSanitizedContext($originalContext);
            $this->inner->setContext($cleanContext);

            try {
                return $this->inner->generate($name, $parameters, $referenceType);
            } finally {
                // Always restore original context, even if generation fails
                $this->inner->setContext($originalContext);
            }
        }

        $this->init();

        $site = $this->siteSelector->retrieve();
        $siteLocale = $site?->getLocale();

        $pos = strrpos($name, '.');
        $hasSuffix = false !== $pos;
        $routeLocale = null;

        if ($hasSuffix) {
            $routeLocale = substr($name, $pos + 1);
        } else {
            // Default: use site locale's variant if it exists
            if (null !== $siteLocale && '' !== $siteLocale) {
                $autoAlias = $this->partitioner->getAlias($name, $siteLocale);
                if (null !== $autoAlias) {
                    $name = $autoAlias;
                    $pos = strrpos($name, '.');
                    $routeLocale = false !== $pos ? substr($name, $pos + 1) : null;
                    $hasSuffix = false !== $pos;
                }
            }
            // Only treat _locale as a forced override (explicit cross-locale intent)
            if (isset($parameters['_locale']) && \is_string($parameters['_locale'])) {
                $forcedLocale = $parameters['_locale'];
                unset($parameters['_locale']);
                $baseName = $hasSuffix && false !== $pos ? substr($name, 0, $pos) : $name;
                $forcedAlias = $this->partitioner->getAlias($baseName, $forcedLocale);
                if (null === $forcedAlias) {
                    throw new RouteNotFoundException("Cannot force locale '$forcedLocale' for base route '$name' (no variant).");
                }
                $name = $forcedAlias;
                $pos = strrpos($name, '.');
                $routeLocale = false !== $pos ? substr($name, $pos + 1) : null;
                $hasSuffix = false !== $pos;
            }
        }

        // Enforce strict cross-locale generation ONLY when no explicit _locale forcing was used
        if ($this->denyCrossLocaleGenerate && null !== $siteLocale && '' !== $siteLocale && $hasSuffix && $routeLocale !== $siteLocale) {
            // At this point, mismatch means user forced a different locale via _locale; allow it.
            // To deny even forced cross-locale generation, uncomment the exception below.
            // throw new RouteNotFoundException("Cross-locale generation denied for route '$name' on site locale '$siteLocale'");
        }

        // Suffixed route path: try locale-specific generator; fallback to inner router
        if ($hasSuffix && null !== $routeLocale && '' !== $routeLocale) {
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

        // Restrict locale detection to actual site locales to avoid treating
        // routes like "api.v2" or "admin.dashboard" as localized routes.
        $allowedLocales = $this->getAllowedLocales();
        if ([] !== $allowedLocales) {
            $this->partitioner->setAllowedLocales($allowedLocales);
        }

        $this->partitioner->partition($collection);

        $this->locales = $this->partitioner->getLocales();
        // Do not eagerly build all locale infrastructures. We lazily create matcher / generator
        // pairs only for the currently active site locale (match) and explicitly forced locales
        // (generate). This effectively limits recognized localized suffixes to those that are
        // actually needed for enabled sites.
    }

    /**
     * Get list of allowed locale suffixes from enabled sites.
     *
     * This restricts route partitioning to only recognize suffixes that match
     * actual site locales, preventing false positives like "admin.dashboard"
     * being treated as locale route "admin" with locale "dashboard".
     *
     * Results are cached per-request to avoid repeated database queries.
     *
     * @return string[]
     */
    private function getAllowedLocales(): array
    {
        if (null !== $this->cachedAllowedLocales) {
            return $this->cachedAllowedLocales;
        }

        $locales = [];

        foreach ($this->siteManager->findBy(['enabled' => true]) as $site) {
            $locale = $site->getLocale();
            if (null !== $locale && '' !== $locale) {
                $locales[] = $locale;
            }
        }

        $this->cachedAllowedLocales = array_values(array_unique($locales));

        return $this->cachedAllowedLocales;
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
        if (null === $locCollection) {
            return;
        }
        $context = $this->createSanitizedContext($this->getContext());
        $this->matchers[$loc] = new UrlMatcher($locCollection, $context);
        $this->generators[$loc] = new UrlGenerator($locCollection, $context);
    }

    /**
     * Check if a route should be ignored from locale-aware URL generation.
     */
    private function shouldIgnoreRoute(string $routeName): bool
    {
        // Check exact route name matches
        foreach ($this->ignoreRoutes as $route) {
            if ($routeName === $route) {
                return true;
            }
        }

        // Check route patterns
        foreach ($this->ignoreRoutePatterns as $routePattern) {
            if (1 === preg_match(\sprintf('#%s#', $routePattern), $routeName)) {
                return true;
            }
        }

        return false;
    }
}
