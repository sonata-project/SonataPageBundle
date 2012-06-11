CHANGELOG
=========

A [BC BREAK] means the update will break the project for many reasons:

* new mandatory configuration
* new dependencies
* class refactoring

### 2012-06-12

* [BC BREAK] Mapped Block::$settings to doctrine JsonType (previously an ArrayType)

    Add the new dependency sonata/doctrine-extensions :

        php composer.phar update

    Migration command :

        php app/console sonata:page:migrate-block-json

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
