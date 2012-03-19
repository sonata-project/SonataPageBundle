CHANGELOG
=========

A [BC BREAK] means the update will break the project for many reasons :

* new mandatory configuration
* new dependencies
* class refactoring

### 2012-03-19

* [BC BREAK] DecoratorStrategy : Update regular expressions, regular expression separators are not fixed in the class.

### 2012-02-28

* [BC BREAK] Move cache and some block to the BlockBundle

    Block names has been updated
        UPDATE `page__bloc` SET `type` = 'sonata.block.service.text' WHERE `type` = 'sonata.page.block.text';
        UPDATE `page__bloc` SET `type` = 'sonata.block.service.action' WHERE `type` = 'sonata.page.block.action';
        UPDATE `page__bloc` SET `type` = 'sonata.block.service.rss' WHERE `type` = 'sonata.page.block.rss';

        republish the snaphsot pages.

* [BC BREAK] Sonata\PageBundle\Block\BaseBlockService does not exist anymore.

    use Sonata\BlockBundle\Block\BaseBlockService;


### 2012-02-18

* [BC BREAK] Integrates the SeoBundle