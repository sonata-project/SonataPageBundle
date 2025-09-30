<?php

declare(strict_types=1);

namespace Sonata\PageBundle\Route;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * RoutePartitioner
 *
 * Responsibility:
 *   - Perform a one-time structural split of the master RouteCollection into
 *     per-locale sub-collections plus the neutral (non‑localized) set.
 *   - Build a fast lookup index for mapping a base (unsuffixed) route name +
 *     locale to its concrete localized route name (base -> [locale => fullName]).
 *
 * Definitions:
 *   Localized route name: <base>.<locale> (last dot separates suffix)
 *   Neutral route name:   does NOT contain a dot OR contains a dot but the last
 *                         fragment is empty / malformed (treated conservatively).
 *
 * Design Goals:
 *   - Zero regex in the hot path; only simple strrpos()/substr().
 *   - Idempotent partition() – safe to call multiple times; subsequent calls no-op.
 *   - Keep the original Route objects (no cloning) – collections are lightweight
 *     references; mutation after partition is not supported or recommended.
 *
 * Usage Pattern (lazy):
 *   $partitioner = new RoutePartitioner();
 *   $partitioner->partition($allRoutes);
 *   $fiCollection = $partitioner->getLocaleCollection('fi');
 *   $enAlias = $partitioner->getAlias('entropy_event_shop', 'en');
 *
 * Thread Safety / Re-entrancy:
 *   Not designed for concurrent mutation. Typical Symfony runtime builds this
 *   exactly once per container lifecycle.
 */
final class RoutePartitioner
{
    /**
     * Neutral (non‑localized) routes.
     */
    private ?RouteCollection $neutral = null;

    /**
     * @var array<string,RouteCollection> Per-locale collections (WITHOUT neutral merged in)
     */
    private array $perLocale = [];

    /**
     * @var array<string,RouteCollection> Cached merged (locale + neutral) collections
     */
    private array $merged = [];

    /**
     * baseName => [ locale => fullRouteName ]
     *
     * @var array<string,array<string,string>>
     */
    private array $baseAliasIndex = [];

    private bool $partitioned = false;

    /**
     * If non-empty, only these locale suffixes are treated as "localized".
     * Empty (default) means accept any suffix shape.
     *
     * @var string[]
     */
    private array $allowedLocales = [];

    /**
     * Perform the partition once.
     */
    public function partition(RouteCollection $all): void
    {
        if ($this->partitioned) {
            return;
        }

        $this->neutral = new RouteCollection();

        foreach ($all->all() as $name => $route) {
            $this->classifyRouteName($name, $route);
        }

        $this->partitioned = true;
    }

    /**
     * Classify a single route name into neutral or localized.
     */
    private function classifyRouteName(string $name, Route $route): void
    {
        $pos = strrpos($name, '.');

        // Neutral if no dot at all.
        if ($pos === false) {
            $this->neutral?->add($name, $route);
            return;
        }

        $base = substr($name, 0, $pos);
        $suffix = substr($name, $pos + 1);

        // Defensive: Treat malformed suffixes as neutral.
        if ($suffix === '' || str_contains($suffix, '/')) {
            $this->neutral?->add($name, $route);
            return;
        }

        // If a whitelist of allowed locales is defined, enforce it.
        if ($this->allowedLocales !== [] && !\in_array($suffix, $this->allowedLocales, true)) {
            $this->neutral?->add($name, $route);
            return;
        }

        // Record localized variant.
        if (!isset($this->perLocale[$suffix])) {
            $this->perLocale[$suffix] = new RouteCollection();
        }
        $this->perLocale[$suffix]->add($name, $route);

        // Index for base alias mapping.
        if (!isset($this->baseAliasIndex[$base])) {
            $this->baseAliasIndex[$base] = [];
        }
        // Last declaration wins (consistent with Symfony's "later override" semantics).
        $this->baseAliasIndex[$base][$suffix] = $name;
    }

    /**
     * Returns the neutral (non-localized) RouteCollection.
     */
    public function getNeutralCollection(): RouteCollection
    {
        if (!$this->partitioned || !$this->neutral instanceof RouteCollection) {
            return new RouteCollection();
        }
        return $this->neutral;
    }

    /**
     * Get the merged per-locale collection (localized + all neutral routes).
     * Merged collections are cached after first build.
     */
    public function getLocaleCollection(string $locale): ?RouteCollection
    {
        if (!$this->partitioned) {
            return null;
        }
        if (!isset($this->perLocale[$locale])) {
            return null;
        }
        if (isset($this->merged[$locale])) {
            return $this->merged[$locale];
        }

        $merged = new RouteCollection();

        // Add all localized routes for this locale.
        foreach ($this->perLocale[$locale]->all() as $name => $route) {
            $merged->add($name, $route);
        }

        // Add neutral routes (if not shadowed by localized names).
        if ($this->neutral) {
            foreach ($this->neutral->all() as $nName => $nRoute) {
                if (!$merged->get($nName)) {
                    $merged->add($nName, $nRoute);
                }
            }
        }

        $this->merged[$locale] = $merged;
        return $merged;
    }

    /**
     * Return true if we have at least one route for the locale.
     */
    public function hasLocale(string $locale): bool
    {
        return isset($this->perLocale[$locale]);
    }

    /**
     * Get all discovered locale codes.
     *
     * @return string[]
     */
    public function getLocales(): array
    {
        return array_keys($this->perLocale);
    }

    /**
     * Map a base (unsuffixed) route name + locale to its concrete localized route name.
     */
    public function getAlias(string $base, string $locale): ?string
    {
        return $this->baseAliasIndex[$base][$locale] ?? null;
    }

    /**
     * Does a base route have ANY localized variants?
     */
    public function hasBase(string $base): bool
    {
        return isset($this->baseAliasIndex[$base]);
    }

    /**
     * Restrict recognized locale suffixes to a finite set (e.g. enabled site locales).
     * Pass an empty array to allow any suffix (default behavior).
     * Must be called BEFORE partition() to have effect.
     *
     * @param string[] $locales
     */
    public function setAllowedLocales(array $locales): void
    {
        if ($this->partitioned) {
            // Silently ignore to avoid partial / inconsistent re-partitioning;
            // caller can instantiate a new partitioner if dynamic change needed.
            return;
        }
        $filtered = [];
        foreach ($locales as $loc) {
            if (\is_string($loc) && $loc !== '') {
                $filtered[] = $loc;
            }
        }
        $this->allowedLocales = array_values(array_unique($filtered));
    }

    /**
     * Light introspection for debugging / tests.
     *
     * @return array{
     *   locales: string[],
     *   neutral_count: int,
     *   localized_counts: array<string,int>,
     *   bases: int
     * }
     */
    public function debugSummary(): array
    {
        $localizedCounts = [];
        foreach ($this->perLocale as $loc => $coll) {
            $localizedCounts[$loc] = $coll->count();
        }

        return [
            'locales' => $this->getLocales(),
            'neutral_count' => $this->neutral?->count() ?? 0,
            'localized_counts' => $localizedCounts,
            'bases' => \count($this->baseAliasIndex),
        ];
    }
}
