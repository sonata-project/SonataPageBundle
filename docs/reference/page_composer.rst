Page Composer
=============

Configure
---------

.. code-block:: yaml

   default_template: default
   templates:
       default:
           path: 'ApplicationSonataPageBundle::demo_layout.html.twig'
           name: 'default'
           containers:
               header:
                   name: Header
               content_top:
                   name: Top content
               content:
                   name: Main content
               content_bottom:
                   name: Bottom content
               footer:
                   name: Footer
           matrix:
               layout: |

                   HHHHHHHH
                   TTTTTTTT
                   TTTTTTTT
                   CCCCCCCC
                   CCCCCCCC
                   BBBBBBBB
                   BBBBBBBB
                   FFFFFFFF

               mapping:
                 H: header
                 T: content_top
                 C: content
                 B: content_bottom
                 F: footer

       2columns:
           path: 'ApplicationSonataPageBundle::demo_2columns_layout.html.twig'
           name: '2 columns layout'
           inherits_containers: default
           containers:
               left_col:
                   name: Left column
                   blocks:
                       - sonata.media.block.media
                       - sonata.media.block.gallery
                       - sonata.media.block.feature_media
               content:
                   name: Main content
                   blocks:
                       - sonata.media.block.media
               right_col:
                   name: Right column
                   blocks:
                       - sonata.news.block.recent_posts
                       - sonata.order.block.recent_orders
                       - sonata.product.block.recent_products
           matrix:
               layout: |

                   HHHHHHHHHH
                   TTTTTTTTTT
                   TTTTTTTTTT
                   LLLCCCCRRR
                   LLLCCCCRRR
                   BBBBBBBBBB
                   BBBBBBBBBB
                   FFFFFFFFFF

               mapping:
                  H: header
                  T: content_top
                  L: left_col
                  R: right_col
                  C: content
                  B: content_bottom
                  F: footer

       3columns:
           path: 'ApplicationSonataPageBundle::demo_3columns_layout.html.twig'
           name: '3 columns layout'
           containers:
               left_col:
                   name: Left column
                   blocks:
                       - sonata.media.block.media
                       - sonata.media.block.gallery
                       - sonata.media.block.feature_media
               mid_col:
                   name: Left column
                   blocks:
                       - sonata.media.block.media
                       - sonata.media.block.gallery
                       - sonata.media.block.feature_media
               right_col:
                   name: Right column
                   blocks:
                       - sonata.news.block.recent_posts
                       - sonata.order.block.recent_orders
                       - sonata.product.block.recent_products
           matrix:
               layout: |
                   LLLMMMMRRR
                   LLLMMMMRRR
                   LLLMMMMRRR
                   LLLMMMMRRR
                   LLLMMMMRRR

               mapping:
                  L: left_col
                  R: right_col
                  M: mid_col


Template inheritance
^^^^^^^^^^^^^^^^^^^^

If you want your template to extend from another template so you don't
have to redeclare all your containers you can use the ``inherits_containers`` option.
This is also shown in the example above.

.. code-block:: yaml

   templates:
       2columns:
           inherits_containers: default
           containers:
               content:
                   name: Main content
                   blocks:
                       - sonata.media.block.media


Template chooser
^^^^^^^^^^^^^^^^

If you have added some custom templates to a block for the ``SonataBlockBundle`` you get a template chooser.

.. code-block:: yaml

   sonata_block:
          acme.demo.block.demo:
              templates:
                 - { name: 'Simple', template: 'AcmeDemoBundle:Block:demo_simple.html.twig' }
                 - { name: 'Big',    template: 'AcmeDemoBundle:Block:demo_big.html.twig' }

Javascript
----------

The **PageComposer** js object trigger various events to allow customization:


**containerloaded**, event properties:

.. code-block:: javascript

   event.containerId // loaded container id


**blockcreated**, event properties:

.. code-block:: javascript

   event.$childBlock // created block jQuery element
   event.parentId    // created block parent id
   event.blockId     // created block id
   event.blockName   // created block name
   event.blockType   // created block type


**blockremoved**, event properties:

.. code-block:: javascript

   event.parentId // removed block parent id


**blockcreateformloaded**, event properties:

.. code-block:: javascript

   event.response    // the raw html response (form)
   event.containerId // current container id
   event.blockType   // selected block type


**blockpositionsupdate**, event properties:

.. code-block:: javascript

   event.disposition // a javascript object containing all child blocks position/ids…


**blockeditformloaded**, event properties:

.. code-block:: javascript

   event.$block // the block jQuery element


**blockparentswitched**, event properties:

.. code-block:: javascript

   event.previousParentId // previous parent block id
   event.newParentId      // new parent block id
   event.blockId          // child block id
