Shared Blocks
=============

By default, the blocks you create to populate your pages are tied to their page.
It means the same block cannot be reused in several pages.

The ``SharedBlockAdmin`` class provides a way to deal with this limitation.
It allows you to manage free blocks that are not linked to pages or to parent blocks just as you usually do with classic page blocks.
This way, shared blocks are common to all your sites.

.. note::

    Changes on a shared block are automatically echoed on all pages which are using it.

Using shared blocks in your pages
---------------------------------

After creating a shared block, you can reference it in your pages by adding a "Shared Block" block.
When configuring this block, you will be asked to select a shared block to use.

When your page will be rendered, the referenced shared block will be displayed in its current state.