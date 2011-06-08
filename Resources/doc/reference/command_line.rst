Command Line Tools
==================

Flush commands
--------------

- Flush cache elements which match the key ``block_id = 5``

    php app/console sonata:page:cache-flush --keys='{"block_id":5}'

- Flush cache elements related to the service ``sonata.page.block.container``

    php app/console sonata:page:cache-flush --service=sonata.page.block.container

- Flush all cache elements

    php app/console sonata:page:cache-flush-all

Page commands
-------------

- Update core routes, from routing files to page manager

    php app/console sonata:page:update-core-routes