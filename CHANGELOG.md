CHANGELOG
=========

A [BC BREAK] means the update will break the project for many reasons:

* new mandatory configuration
* new dependencies
* class refactoring

### 2014-11-04

* [BC BREAK] Move assets management (js, css) to gulp and move css to sass.
  You have now the following files available:

  styles:

  * sonata-page.back.css      *BO stylesheet*
  * sonata-page.back.min.css  *BO minified stylesheet*
  * sonata-page.front.css     *frontend stylesheet*
  * sonata-page.front.min.css *frontend minified stylesheet*

  javascripts:

  * sonata-page.back.js      *BO javascript*
  * sonata-page.back.min.js  *BO minified javascript*
  * sonata-page.front.js     *frontend javascript*
  * sonata-page.front.min.js *frontend minified javascript*

### 2014-06-11

* Add a RequestFactory to configure a valid Request (and configure the Request::$factory) to support Symfony 2.5

### 2014-04-23

* [BC BREAK] The default context used with BlockBundle is now ``sonata_page_bundle``, it was ``cms`` before.

### 2014-01-02

* Changed twig template ``Resources/views/base_layout.html.twig``. Added Bootstrap3 classes while keeping Bootstrap2 classes (this shouldn't break BC).


### 2013-10-22

* remove the ``use_streamed_response`` option to fix cache handling, it is not possible to compute cache with a streaming response

### 2013-07-30
* [BC BREAK] Inline edition has been deprecated, see UPGRADE-2.3 for more information.
* It is now possible to add block from the admin backend

### 2012-12-13

* Add new block in the base_layout.html.twig file. Add new deprecated blocks

### 2012-12-13

* [BC BREAK] A new ``type`` field has been added in Page and Snapshot models. This new field is used to
  identify a ``page service`` that manages pages for a given type.

  Database Migration: (replace table name)

      ALTER TABLE page__page ADD type VARCHAR(255) DEFAULT NULL;
      ALTER TABLE page__snapshot ADD type VARCHAR(255) DEFAULT NULL;

* The ``PageRenderer`` class has been dropped and replaced by the page service workflow. When a page must be rendered,
  it now uses the page service associated to the page type. A default page service has been created to provide the
  same behavior as the previous workflow so there is not need to specify a page type for existing pages.

* The templates management has also been completely refactored into a ``TemplateManager`` class. This class is now
  responsible to render a template code.

### 2012-10-23

* [BC BREAK] The front page editor has been completely redesigned.

  Assets ``page.js`` and ``page.css`` have been rewritten to provide new functionality to the front page editor.

  Templates for base blocks and container blocks have been updated to provide additional information on blocks when
  in the front editor mode.

  ``BlockInteractor`` has its ``saveBlocksPosition()`` method updated. It now performs unit updates and does not
  require a tree structure anymore.

  The BlockManager interface and class have been updated to implement a ``updatePosition()`` method.

* [BC BREAK] The container layout setting is now used to decorate inside the block div instead of decorating
  outside.

### 2012-09-14

* [BC BREAK] Integrate the SymfonyCmfRoutingExtraBundle from the CMF project

   ``sonata_page_url`` will raise an exception, just use the ``path`` twig helper

   No more ``catchAll`` routing, now the routing is handled by the ``ChainRouter`` service

   Introduce a ``pageAlias`` field, so this field will be used to generate an url using the
   a code defined in the ``Page`` entity. This can be a nice feature if you want to generate a
   link from a template but without knowing the url defined by an user in the backend.

   For performance issue, the ``pageAlias`` must be prefixed by ``_page_alias_``,
   this will avoid extra database lookup to occurs, so from a template you must call
   the an alias like this ``path('_page_alias_homepage')``

   The ``PageController::catchAll`` has been removed.

   Execute the following migrations

        ALTER TABLE page__page ADD page_alias VARCHAR(255) DEFAULT NULL
        ALTER TABLE page__snapshot ADD page_alias VARCHAR(255) DEFAULT NULL

   Add the CMF bundle into the AppKernel.php

        new Symfony\Cmf\Bundle\RoutingExtraBundle\SymfonyCmfRoutingExtraBundle()

### 2012-08-31

* [BC BREAK] Change prototype of "PageExtension::url" method and so "sonata_page_url" Twig helper.

    Before: sonata_page_url(page, absolute)
    After:  sonata_page_url(page, {'param1': 'value1', ...}, absolute)

### 2012-08-24

* [BC BREAK] Block::$settings "name" property is now a "code" property.

    Database Migration: (replace table name)

        ALTER TABLE page__bloc ADD name VARCHAR(255) DEFAULT NULL;

    Migration command: (change entity class as required)

        php app/console sonata:page:migrate-block-name-setting --class="Application\Sonata\PageBundle\Entity\Block"

    Optionally, set the --update-name parameter to true to update the "name" field with the old "name" setting.

        php app/console sonata:page:migrate-block-name-setting --update-name=true

### 2012-06-12

* [BC BREAK] Mapped Block::$settings to doctrine JsonType (previously an ArrayType)

    Add the new dependency sonata/doctrine-extensions :

        php composer.phar update

    Migration command :

        php app/console sonata:page:migrate-block-json --table page__bloc
        php app/console sonata:page:migrate-block-json --table page__bloc_audit

* Mapped Snapshot::$content to JsonType (previously a manually encoded json string)

### 2012-04-09

* [BC BREAK] The page bundle has now a dependency to the SonataNotificationBundle to run the snapshot task asynchronously.

    Command changes :

        app/console sonata:page:create-snapshots --site=all --mode=sync    # default mode (BC)
        app/console sonata:page:create-snapshots --site=all --mode=async

### 2012-03-23

* [BC BREAK] prefix internal route to _page_internal_*, all internals routes must be renamed to include the correct prefix

    SQL Update :

        UPDATE `page__page`
        SET `route_name` = CONCAT('_page_internal_', route_name)
        WHERE url IS NULL AND route_name != 'cms_page' AND SUBSTR(route_name, 1, 14) <> '_page_internal'

        republish the snaphsot pages.

### 2012-03-21

* Add SEO fields to Site and alter the SeoPage information

### 2012-03-19

* [BC BREAK] DecoratorStrategy : Update regular expressions, regular expression separators are not fixed in the class.
* Add a twig global variable sonata_page (sonata_page.cmsmanager, sonata_page.siteavailables, sonata_page.currentsite)
* Add locale to Site model, if the locale is set an _locale attribute is set to the request and no site available for
  the current url, then the user is redirected to the default site a (ie, / => /en, if /en is the default)

### 2012-02-28

* [BC BREAK] Move cache and some block to the BlockBundle

    Block names has been updated:

        UPDATE `page__bloc` SET `type` = 'sonata.block.service.text'   WHERE `type` = 'sonata.page.block.text';
        UPDATE `page__bloc` SET `type` = 'sonata.block.service.action' WHERE `type` = 'sonata.page.block.action';
        UPDATE `page__bloc` SET `type` = 'sonata.block.service.rss'    WHERE `type` = 'sonata.page.block.rss';

        republish the snaphsot pages.

* [BC BREAK] Sonata\PageBundle\Block\BaseBlockService does not exist anymore.

    use Sonata\BlockBundle\Block\BaseBlockService;


### 2012-02-18

* [BC BREAK] Integrates the SeoBundle

### 2013-12-13

* PageManager, BlockManager, SnapshotManager & SiteManager now extend the DoctrineBaseManager (from SonataCoreBundle).
