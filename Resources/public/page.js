/**
 *
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

jQuery(document).ready(function() {
    Page.init();
});

var Page = {
    blocks: null,
    log: function(message) {
      try {
          console.log(message);
      } catch(e) {

      }
    },
    init: function() {
        jQuery('#page-action-enabled-edit').change(Page.handleClickEnabledEdit);
        jQuery('#page-action-save-position').click(Page.savePosition);

        jQuery('#page-action-save-position').hide();

        Page.blocks = jQuery('div.cms-block-element, div.cms-container-root');
        Page.blocks.mouseover(Page.handleBlockHover);
        Page.blocks.dblclick(Page.handleBlockClick);

        Page.buildHandler();
    },

    handleBlockClick: function(event) {
        event.stopPropagation();

        var id = event.currentTarget.id.slice(10);
        window.open(Page.url.block_edit.replace(/BLOCK_ID/, id), '_newtab');
    },

    handleBlockHover: function(event) {
        event.stopPropagation();
        Page.blocks.removeClass('cms-block-hand-over');
        jQuery(this).addClass('cms-block-hand-over');
    },

    handleClickEnabledEdit: function(event) {
        if (event.currentTarget.checked) {
            jQuery('#page-action-save-position').show();
            jQuery("div.cms-container").sortable('enable');

            jQuery('body').addClass('cms-edit-mode');

        } else {
            jQuery('#page-action-save-position').hide();
            jQuery("div.cms-container").sortable('disable');

            jQuery('body').removeClass('cms-edit-mode');
        }

    },
    buildHandler: function() {
        jQuery("div.cms-container").sortable({
            connectWith: "div.cms-container",
            items: "div.cms-block-element",
            placeholder: 'cms-block-placeholder',
            helper: 'clone',
            dropOnEmpty: true,
            forcePlaceholderSize: 100,
            cursor: 'move',
            start: function(event, ui) {
                jQuery('div.cms-container').addClass('cms-container-start');
                jQuery('div.cms-block-element').addClass('cms-block-start');
                jQuery('div.cms-fake-block').css('display', 'block');
            },
            stop: function(event, ui) {
                jQuery('div.cms-container').removeClass('cms-container-start');
                jQuery('div.cms-block-element').removeClass('cms-block-start');
                jQuery('div.cms-fake-block').css('display', 'none');
            }
        }).sortable('disable');
    },

    buildInformation: function() {

        var blocks = jQuery("div.cms-container-root");
        var information = {};

        blocks.each(function(i, block) {
            information[block.id] = {
                type: block.getAttribute('type'),
                child: Page.buildBlockInformation(block)
            }
        });

        return information;
    },

    buildBlockInformation: function(block) {
        if (!block.id) {
            return;
        }

        var blocks = jQuery('#' + block.id + ' > div.cms-block');
        var information = {};

        blocks.each(function(i, child) {
            information[child.id] = {
                type: child.getAttribute('type'),
                child: Page.buildBlockInformation(child)
            }
        });

        return information;
    },

    savePosition: function() {
        var params = {
            disposition: Page.buildInformation()
        };

        jQuery.ajax({
            type: 'POST',
            url: Page.url.block_save_position,
            data: params,
            dataType: 'json',
            success: function() {
                alert('object saved !')
            },
            error: function() {
                alert('Error');
            }
        });
    }

}