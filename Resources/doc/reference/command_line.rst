Command Line Tools
==================

Flush commands
--------------

- Flush cache elements matching the key ``block_id = 5``::

    php app/console sonata:cache:flush --keys='{"block_id":5}'

- Flush all cache elements::

    php app/console sonata:cache:flush-all

For more information about this, please have a look at the ``SonataCacheBundle`` documentation.

Page commands
-------------

- Update core routes, from routing files to page manager::

    php app/console sonata:page:update-core-routes --site=all

- Create snapshots from defined pages::

    php app/console sonata:page:create-snapshots --site=all

- Cleanup snapshots::

    php app/console sonata:page:cleanup-snapshots --site=all --keep-snapshots=5

Please note that you can also give multiple website identifiers for all those commands, this way:

    php app/console sonata:page:update-core-routes --site=1 --site=2 --site=...
    php app/console sonata:page:create-snapshots --site=1 --site=2 --site=...
    php app/console sonata:page:cleanup-snapshots --site=1 --site=2 --site=...

Debug Commands
--------------

- Print page composition::

    php app/console sonata:page:dump-page sonata.page.cms.snapshot PAGE_ID
    php app/console sonata:page:dump-page sonata.page.cms.snapshot PAGE_ID --extended


- Render a block::

    php app/console sonata:page:render-block sonata.page.cms.snapshot PAGE_ID BLOCK_ID
    php app/console sonata:page:render-block sonata.page.cms.page PAGE_ID BLOCK_ID
