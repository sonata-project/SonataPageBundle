UPGRADE 4.x
===========

## Localized Routing Support

### Added support for Symfony native localized routes

The bundle now includes a `SiteAwareRouter` that provides native support for Symfony's localized routing with per-locale URL prefixes.

#### New Features

- **Route Partitioning**: Routes are automatically partitioned by locale suffix (e.g., `app_home.en`, `app_home.fi`)
- **Structural 404s**: Wrong-locale URLs return 404 instead of redirecting or falling back
- **Locale Filtering**: Only routes matching enabled site locales are recognized as localized routes
- **Consistent Prefixing**: Both CMS pages and Symfony routes use the same locale prefix mechanism

#### Configuration

This feature is **automatically enabled** and requires no configuration changes. However, to use it:

1. Define your routes with locale suffixes:

```yaml
# config/routes.yaml
app_home.fi:
    path: /
    controller: App\Controller\HomeController::index

app_home.en:
    path: /en
    controller: App\Controller\HomeController::index
```

2. Configure your sites with `relativePath`:

```php
$finnishSite->setLocale('fi');
$finnishSite->setRelativePath('');  // Root path

$englishSite->setLocale('en');
$englishSite->setRelativePath('/en');  // /en prefix
```

#### Behavioral Changes

- **Route Name Convention**: Routes like `admin.dashboard` are now treated as neutral routes (not localized). If you need a localized route, use the format `{base_name}.{locale}` (e.g., `admin_dashboard.en`).
- **URL Generation**: Calling `generateUrl('app_home')` automatically resolves to the current site's locale variant (e.g., `app_home.fi` or `app_home.en`).
- **Hybrid Pages**: The `sonata:page:update-core-routes` command now filters routes by locale, preventing duplicate hybrid pages across sites.

#### Migration Guide

**If you're NOT using localized routing:**
- No changes needed. Your existing routes continue to work as before.
- Routes without locale suffixes are treated as neutral routes (available to all sites).

**If you WANT to use localized routing:**

1. Create locale-specific route variants:

```yaml
# Before
app_products:
    path: /products

# After
app_products.fi:
    path: /tuotteet

app_products.en:
    path: /en/products
```

2. Update site configuration with locale prefixes:

```php
$site->setRelativePath('/en');  // For English site
```

3. Regenerate hybrid pages:

```bash
bin/console sonata:page:update-core-routes --site=all
bin/console sonata:page:create-snapshots --site=all
```

4. Update any hardcoded route names in your code to use locale-aware generation:

```php
// Before
$url = $this->generateUrl('app_products');

// After (same code, but now locale-aware)
$url = $this->generateUrl('app_products');  // Automatically resolves to .fi or .en variant
```

#### Potential Issues

**Routes with dots in the name:**
- Routes like `api.v2` or `admin.config` are now treated as neutral routes.
- If these should be localized, rename them to follow the convention: `api_v2.en`, `admin_config.fi`.

**Existing multisite setups:**
- If you have sites with `relativePath` already configured, ensure your route paths include the locale prefix to avoid double prefixes.

For detailed information, see the [Localized Routing documentation](docs/reference/localized_routing.rst).
