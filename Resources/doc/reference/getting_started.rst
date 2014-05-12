Getting Started
===============

The bundle works on top of 3 simple models :
 * a ``Page``\ : A page is composed of Blocks and contains information about a
   page (routing, headers, etc...)
 * a ``Block``\ : A block contains information about an area of a page, a block
   can have children
 * a ``Snapshot``\ : The final representation of a page, the end user always
   sees a Snapshot


Creating a default site
-----------------------

First, you need to create a dedicated site, let's create a localhost site:

.. code-block:: bash

    php app/console sonata:page:create-site

Or:

.. code-block:: bash

    php app/console  sonata:page:create-site --enabled=true --name=localhost --locale=- --host=localhost --relativePath=/ --enabledFrom=now --enabledTo="+10 years" --default=true


The output might look like this::

    Please define a value for Site.name : localhost
    Please define a value for Site.host : localhost
    Please define a value for Site.relativePath : /
    Please define a value for Site.enabled : true
    Please define a value for Site.locale : -
    Please define a value for Site.enabledFrom : now
    Please define a value for Site.enabledTo : +10 years
    Please define a value for Site.default : true

    Creating website with the following information :
      name : localhost
      site : http(s)://localhost
      enabled :  Tue, 10 Jan 2012 16:12:08 +0100 => Mon, 10 Jan 2022 16:12:08 +0100

    Confirm site creation ?y

    Site created !

    You can now create the related pages and snapshots by running the followings commands:
      php app/console sonata:page:update-core-routes --site=42
      php app/console sonata:page:create-snapshots --site=42

Note: The following fields must have values specified when creating a site:

+--------------+----------+-------------------------------------------------------------------------------+
| Field Name   | Type     | Description                                                                   |
+==============+==========+===============================================================================+
| name         | string   | Friendly name for the site.                                                   |
+--------------+----------+-------------------------------------------------------------------------------+
| host         | string   | Hostname used to reach the site.                                              |
+--------------+----------+-------------------------------------------------------------------------------+
| relativePath | string   | The relative path for the site (only used by the HostPathSiteSelector;        |
|              |          | otherwise, just use '/').                                                     |
+--------------+----------+-------------------------------------------------------------------------------+
| enabled      | boolean  | If the site is enabled or not (true values are 'true', 1, '1'; all other      |
|              |          | values default to false).                                                     |
+--------------+----------+-------------------------------------------------------------------------------+
| enabledFrom  | DateTime | The Date/Time the site is enabled from (if site is always enabled and has no  |
|              |          | start Date/Time, use '-' as the value).                                       |
+--------------+----------+-------------------------------------------------------------------------------+
| enabledTo    | DateTime | The Date/Time the site is enabled to (if site is always enabled and has no    |
|              |          | end Date/Time, use '-' as the value).                                         |
+--------------+----------+-------------------------------------------------------------------------------+
| default      | boolean  | Only used by the HostPathSiteSelector as the default site if it is unable to  |
|              |          | match any other site (true values are 'true', 1, '1'; all other values        |
|              |          | default to false).                                                            |
+--------------+----------+-------------------------------------------------------------------------------+
| locale       | string   | The default locale for the site (use '-' as the value if specifying the       |
|              |          | locale is not needed).                                                        |
+--------------+----------+-------------------------------------------------------------------------------+


Creating default pages
----------------------

As the Page bundle can handle symfony actions, actions need to be registered, just run the commands:

.. code-block:: bash

    php app/console sonata:page:update-core-routes --site=all

The output might look like this::

    Updating/Creating hybrid pages
    --------------------------------------------------------------------------------
    UPDATE  homepage                                           /
    UPDATE  fos_user_security_login                            /login
    UPDATE  fos_user_security_check                            /login_check
    UPDATE  fos_user_security_logout                           /logout
    UPDATE  fos_user_profile_show                              /profile/
    UPDATE  fos_user_profile_edit                              /profile/edit
    UPDATE  fos_user_registration_register                     /register/
    UPDATE  fos_user_registration_check_email                  /register/check-email
    UPDATE  fos_user_registration_confirm                      /register/confirm/{token}
    UPDATE  fos_user_registration_confirmed                    /register/confirmed
    UPDATE  fos_user_resetting_request                         /resetting/request
    UPDATE  fos_user_resetting_send_email                      /resetting/send-email
    UPDATE  fos_user_resetting_check_email                     /resetting/check-email
    UPDATE  fos_user_resetting_reset                           /resetting/reset/{token}
    UPDATE  fos_user_change_password                           /change-password/change-password
    UPDATE  sonata_media_gallery_index                         /media/gallery/
    UPDATE  sonata_media_gallery_view                          /media/gallery/view/{id}
    UPDATE  sonata_media_view                                  /media/view/{id}/{format}
    CREATE  sonata_media_download                              /media/download/{id}/{format}
    UPDATE  sonata_news_add_comment                            /blog/add-comment/{id}
    UPDATE  sonata_news_archive_monthly                        /blog/archive/{year}/{month}.{_format}
    UPDATE  sonata_news_tag                                    /blog/tag/{tag}.{_format}
    CREATE  sonata_news_category                               /blog/category/{category}.{_format}
    UPDATE  sonata_news_archive_yearly                         /blog/archive/{year}.{_format}
    UPDATE  sonata_news_archive                                /blog/archive.{_format}
    UPDATE  sonata_news_view                                   /blog/{permalink}.{_format}
    UPDATE  sonata_news_home                                   /blog/
    CREATE  sonata_news_comment_moderation                     /blog/comment/moderation/{commentId}/{hash}/{status}
    UPDATE  catchAll                                           /{path}

    Some hybrid pages do not exist anymore
    --------------------------------------------------------------------------------
    ERROR   sonata_news_archive_daily
    ERROR   global

      *WARNING* : Pages has been updated however some pages do not exist anymore.
                  You must remove them manually.

The command will print updated and created pages. The last part of the command
displays outdated actions, so depending on the change some dedicated actions must
be taken.

Creating default snapshots
--------------------------

At this point, no snapshots are available so the end user will get an error. The
following command need to be run:

.. code-block:: bash

    php app/console sonata:page:create-snapshots --site=all

The output might look like this::

    001/038 /hello/{name}                                      ... OK !
    002/038 /                                                  ... OK !
    003/038 /login                                             ... OK !
    004/038 /login_check                                       ... OK !
    005/038 /logout                                            ... OK !
    006/038 /profile/                                          ... OK !
    007/038 /profile/edit                                      ... OK !
    008/038 /register/                                         ... OK !
    009/038 /register/check-email                              ... OK !
    010/038 /register/confirm/{token}                          ... OK !
    011/038 /register/confirmed                                ... OK !
    [...]
    035/038 /blog/                                             ... OK !
    036/038 /media/download/{id}/{format}                      ... OK !
    037/038 /blog/category/{category}.{_format}                ... OK !
    038/038 /blog/comment/moderation/{commentId}/{hash}/{status} ... OK !

    Enabling snapshots ... OK !

The command will take ``Page``\ s and then create the related ``Snapshot``\ s. At
this point the front is available for the end user.

Add or Edit a Block
-------------------

Before adding a new block, please look to the default layout
``SonataPageBundle::layout.html.twig``, it contains different method calls.

* ``sonata_page_render_container('content', page)``\ : render the container
  ``content`` of the current page
* ``sonata_page_render_container('content_bottom', 'global')``\ : render the
  container ``content_bottom`` of the global page.

  A global page does not belong to the current url but it can be used on different pages.
* ``page_include_stylesheets`` and ``page_include_javascripts``\ : insert the
  stylesheets and javascripts used on the page by the related blocks.

The block management is done from the front end: a block can be moved and
edited this way:

* login into the backend using a valid user
* go back to the front,
* you should see a black navigation bar
* click on 'Show Zone'
* some areas are now available, just double click on an area (a block container)
* from the new interface you can add inner blocks and save the bock container.
* refresh the front page, you should see the new blocks.

To add a new container block, simply render the container with the desired name:

``sonata_page_render_container('footer_left', 'global')``

When the SonataPageBundle renders this container, it will automatically insert
a ``sonata.page.block.container`` with the name ``footer_left`` if it does not exist
yet. You will then be able to add new child blocks to it in the frontend. The
second parameter is the name of the page. It can be either a string, or a
``Page`` instance. In case of a string, it will create a new  page with that name
if it doesn't already exist.

Publish a snapshot
------------------

The blocks added are not accessible to a non connected user, the blocks are
published trough the ``Snapshot`` model. So once the new page is built or
updated and ready to go live, just click on 'Create publication'.
