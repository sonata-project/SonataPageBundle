Command Line Tools
==================

Flush commands
--------------

- Flush cache elements matching the key ``block_id = 5``::

    php app/console sonata:page:cache-flush snapshot --keys='{"block_id":5}'

- Flush cache elements related to the service ``sonata.page.block.container``::

    php app/console sonata:page:cache-flush snapshot --service=sonata.page.block.container

- Flush all cache elements::

    php app/console sonata:page:cache-flush-all snapshot

Page commands
-------------

- Update core routes, from routing files to page manager::

    php app/console sonata:page:update-core-routes --site=all

- Create snapshots from defined pages::

    php app/console sonata:page:create-snapshots --site=all

Debug Commands
--------------

- Print page composition::

    php app/console sonata:page:dump-page sonata.page.cms.snapshot PAGE_ID
    php app/console sonata:page:dump-page sonata.page.cms.snapshot PAGE_ID --extended


- Render a block::

    php app/console sonata:page:render-block sonata.page.cms.snapshot PAGE_ID BLOCK_ID
    php app/console sonata:page:render-block sonata.page.cms.page PAGE_ID BLOCK_ID
