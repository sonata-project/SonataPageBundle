Performance
===========

This page try to list any good tips to improve performance.

Indexes
~~~~~~~

The Doctrine ORM cannot defined indexes for ``varchar`` as the Doctrine's metadata framework does not accept the
length requirement. So if you want to speed up queries you need to manually add indexes:

.. code-block:: sql

    ALTER TABLE `page__snapshot` ADD INDEX `idx_snapshot_route_name` (`route_name` (32), `site_id`);
    ALTER TABLE `page__snapshot` ADD INDEX `idx_snapshot_page_alias` (`page_alias` (32), `site_id`);
    ALTER TABLE `page__snapshot` ADD INDEX `idx_snapshot_url` (`url` (32), `site_id`);
    ALTER TABLE `page__page` ADD INDEX `idx_page_route_name` (`route_name` (32), `site_id`);
    ALTER TABLE `page__page` ADD INDEX `idx_page_page_alias` (`page_alias` (32), `site_id`);
    ALTER TABLE `page__page` ADD INDEX `idx_page_url` (`url` (32), `site_id`);

Snapshots
~~~~~~~~~

If your application contains a lot of snapshots which are not used anymore, this can slowdown the database server.
You can clean up old snapshots by running the command:

.. code-block:: bash

    php app/console sonata:page:cleanup-snapshots --site=all --keep-snapshots=5
