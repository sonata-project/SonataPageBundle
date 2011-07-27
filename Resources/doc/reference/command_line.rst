Command Line Tools
==================

Flush commands
--------------

- Flush cache elements matching the key ``block_id = 5``

    php app/console sonata:page:cache-flush snapshot --keys='{"block_id":5}'

- Flush cache elements related to the service ``sonata.page.block.container``

    php app/console sonata:page:cache-flush snapshot --service=sonata.page.block.container

- Flush all cache elements

    php app/console sonata:page:cache-flush-all snapshot

Page commands
-------------

- Update core routes, from routing files to page manager

    php app/console sonata:page:update-core-routes

- Create snapshots from defined pages

    php app/console sonata:page:create-snapshots

