/**
 *
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * generated on: Thu Nov 06 2014 00:08:07 GMT+0100 (CET)
 * revision:     71deefa3b32e338654bad5b904239358daf1d7e6
 *
 */
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

(function ($, global) {

    /**
     * PageComposer class.
     *
     * @constructor
     */
    var PageComposer = function (pageId, options) {
        var settings = options || {};

        this.pageId             = pageId;
        this.$container         = $('.page-composer');
        this.$dynamicArea       = $('.page-composer__dyn-content');
        this.$pagePreview       = $('.page-composer__page-preview');
        this.$containerPreviews = this.$pagePreview.find('.page-composer__page-preview__container');
        this.routes             = $.extend({}, settings.routes       || {});
        this.translations       = $.extend({}, settings.translations || {});
        this.csrfTokens         = {};

        this.bindPagePreviewHandlers();
        this.bindOrphansHandlers();

        // attach event listeners
        var self  = this,
            $this = $(this);
        $this.on('containerclick', function (e) {
            self.loadContainer(e.$container);
        });
        $this.on('containerloaded',       this.handleContainerLoaded);
        $this.on('blockcreated',          this.handleBlockCreated);
        $this.on('blockremoved',          this.handleBlockRemoved);
        $this.on('blockcreateformloaded', this.handleBlockCreateFormLoaded);
        $this.on('blockpositionsupdate',  this.handleBlockPositionsUpdate);
        $this.on('blockeditformloaded',   this.handleBlockEditFormLoaded);
        $this.on('blockparentswitched',   this.handleBlockParentSwitched);
    };

    /**
     * Apply all Admin required functions.
     *
     * @param $context
     */
    function applyAdmin($context) {
        if (typeof global.admin != 'undefined') {
            return;
        }

        Admin.shared_setup($context);
    }

    PageComposer.prototype = {
        /**
         * Translates given label.
         *
         * @param {String} label
         * @return {String}
         */
        translate: function (label) {
            if (this.translations[label]) {
                return this.translations[label];
            }

            return label;
        },

        /**
         * @param id
         * @param parameters
         * @returns {*}
         */
        getRouteUrl: function (id, parameters) {
            if (!this.routes[id]) {
                throw new Error('Route "' + id + '" does not exist');
            }

            var url = this.routes[id];
            for (var paramKey in parameters) {
                url = url.replace(new RegExp(paramKey), parameters[paramKey]);
            }

            return url;
        },

        /**
         * Check if the given form element name attribute match specific type.
         * Used because form element names are 'hashes' (s5311aef39e552[name]).
         *
         * @param name
         * @param type
         * @returns {boolean}
         */
        isFormControlTypeByName: function (name, type) {
            if (typeof name != 'undefined') {
               var position  = name.length,
                   search    = '[' + type + ']',
                   lastIndex = name.lastIndexOf(search),
                   position  = position - search.length;

               return lastIndex !== -1 && lastIndex === position;
            }

            return false;
        },

        /**
         * Called when a child block has been created.
         * The event has the following properties:
         *
         *    $childBlock The child container dom element
         *    parentId    The parent block id
         *    blockId     The child block id
         *    blockName   The block name
         *    blockType   The block type
         *
         * @param event
         */
        handleBlockCreated: function (event) {
            var self = this;
            $.ajax({
                url:     this.getRouteUrl('block_preview', { 'BLOCK_ID': event.blockId }),
                type:    'GET',
                success: function (resp) {
                    var $content = $(resp);
                    event.$childBlock.replaceWith($content);
                    self.controlChildBlock($content);

                    // refresh parent block child count
                    var newChildCount = self.getContainerChildCountFromList(event.parentId);
                    if (newChildCount !== null) {
                        self.updateChildCount(event.parentId, newChildCount);
                    }
                },
                error: function () {
                    self.containerNotification('an error occured while fetching block preview', 'error', true);
                }
            });
        },

        /**
         * Remove given block.
         *
         * @param event
         */
        handleBlockRemoved: function (event) {
            // refresh parent block child count
            var newChildCount = this.getContainerChildCountFromList(event.parentId);
            if (newChildCount !== null) {
                this.updateChildCount(event.parentId, newChildCount);
            }
        },

        /**
         * Display notification for current block container.
         *
         * @param message
         * @param type
         * @param persist
         */
        containerNotification: function (message, type, persist) {
            var $notice = this.$dynamicArea.find('.page-composer__container__view__notice');
            if ($notice.length === 1) {
                if (this.containerNotificationTimer) {
                    clearTimeout(this.containerNotificationTimer);
                }
                $notice.removeClass('persist success error');
                if (type) {
                    $notice.addClass(type);
                }
                $notice.text(message);
                $notice.show();
                if (persist !== true) {
                    this.containerNotificationTimer = setTimeout(function () {
                        $notice.hide().empty();
                    }, 2000);
                } else {
                    var $close = $('<span class="close-notice">x</span>');
                    $close.on('click', function () {
                        $notice.hide().empty();
                    });
                    $notice.addClass('persist');
                    $notice.append($close);
                }
            }
        },

        /**
         * Save block positions.
         * event.disposition contains positions data:
         *
         *    [
         *      { id: 126, page_id: 2, parent_id: 18, position: 0 },
         *      { id: 21,  page_id: 2, parent_id: 18, position: 1 },
         *      ...
         *    ]
         *
         * @param event
         */
        handleBlockPositionsUpdate: function (event) {
            var self = this;
            this.containerNotification('saving block positions…');
            $.ajax({
                url:  this.getRouteUrl('save_blocks_positions'),
                type: 'POST',
                data: { disposition: event.disposition },
                success: function (resp) {
                    if (resp.result && resp.result === 'ok') {
                        self.containerNotification('block positions saved', 'success');
                    }
                },
                error: function () {
                    self.containerNotification('an error occured while saving block positions', 'error', true);
                }
            });
        },

        /**
         * Called when a block parent has changed (typically on drag n' drop).
         * The event has the following properties:
         *
         *    previousParentId
         *    newParentId
         *    blockId
         *
         * @param event
         */
        handleBlockParentSwitched: function (event) {
            var $previousParentPreview  = $('.block-preview-' + event.previousParentId),
                $oldChildCountIndicator = $previousParentPreview.find('.child-count'),
                oldChildCount           = parseInt($oldChildCountIndicator.text().trim(), 10),
                $newParentPreview       = $('.block-preview-' + event.newParentId),
                $newChildCountIndicator = $newParentPreview.find('.child-count'),
                newChildCount           = parseInt($newChildCountIndicator.text().trim(), 10);

            this.updateChildCount(event.previousParentId, oldChildCount - 1);
            this.updateChildCount(event.newParentId,      newChildCount + 1);
        },

        /**
         * Compute child count for the given block container id.
         *
         * @param containerId
         * @returns {number}
         */
        getContainerChildCountFromList: function (containerId) {
            var $blockView = this.$dynamicArea.find('.block-view-' + containerId);

            if ($blockView.length === 0) {
                return null;
            }

            var $children = $blockView.find('.page-composer__container__child'),
                childCount = 0;

            $children.each(function () {
                var $child  = $(this),
                    blockId = $child.attr('data-block-id');
                if (typeof blockId != 'undefined') {
                    childCount++;
                }
            });

            return childCount;
        },

        /**
         * Update child count for the given container block id.
         *
         * @param blockId
         * @param count
         */
        updateChildCount: function (blockId, count) {
            var $previewCount = $('.block-preview-' + blockId),
                $viewCount    = $('.block-view-' + blockId);

            if ($previewCount.length > 0) {
                $previewCount.find('.child-count').text(count);
            }

            if ($viewCount.length > 0) {
                $viewCount.find('.page-composer__container__child-count span').text(count);
            }
        },

        /**
         * Handler called when block creation form is received.
         * Makes the form handled through ajax.
         *
         * @param containerId
         * @param blockType
         */
        handleBlockCreateFormLoaded: function (event) {
            var self               = this,
                $containerChildren = this.$dynamicArea.find('.page-composer__container__children'),
                $container         = this.$dynamicArea.find('.page-composer__container__main-edition-area');

            if (event.container) {
                var $childBlock   = event.container,
                    $childContent = $childBlock.find('.page-composer__container__child__content');
                $childContent.html(event.response);
            } else {
                var $childBlock = $(['<li class="page-composer__container__child">',
                        '<a class="page-composer__container__child__edit">',
                            '<h4 class="page-composer__container__child__name">',
                                '<input type="text" class="page-composer__container__child__name__input">',
                            '</h4>',
                        '</a>',
                        '<div class="page-composer__container__child__right">',
                            '<span class="badge">' + event.blockTypeLabel + '</span>',
                        '</div>',
                        '<div class="page-composer__container__child__content">',
                        '</div>',
                    '</li>'].join('')),
                    $childContent = $childBlock.find('.page-composer__container__child__content');

                $childContent.append(event.response);
                $containerChildren.append($childBlock);

                $childContent.show();
            }

            var $form         = $childBlock.find('form'),
                formAction    = $form.attr('action'),
                formMethod    = $form.attr('method'),
                $formControls = $form.find('.hidden').find('input, select, textarea'),
                $formActions  = $form.find('.form-actions'),
                $childName    = this.$dynamicArea.find('.page-composer__container__child__name'),
                $nameFormControl,
                $parentFormControl,
                $positionFormControl;

            applyAdmin($form);

            $(document).scrollTo($childBlock, 200);

            $container.show();

            // scan form elements to find name/parent/position,
            // then set value according to current container and hide it.
            $formControls.each(function () {
                var $formControl    = $(this),
                    formControlName = $formControl.attr('name');

                if (self.isFormControlTypeByName(formControlName, 'name')) {
                    $nameFormControl = $formControl;
                    $childName.find('.page-composer__container__child__name__input').bind("propertychange keyup input paste", function (e) {
                        $nameFormControl.val($(this).val());
                    });
                } else if (self.isFormControlTypeByName(formControlName, 'parent')) {
                    $parentFormControl = $formControl;
                    $parentFormControl.val(event.containerId);
                    $parentFormControl.parent().parent().hide();
                } else if (self.isFormControlTypeByName(formControlName, 'position')) {
                    $positionFormControl = $formControl;
                    $positionFormControl.val($containerChildren.find('> *').length);
                    $positionFormControl.closest('.form-group').hide();
                }
            });

            $formActions.each(function () {
                var $formAction   = $(this),
                    $cancelButton = $('<span class="btn btn-warning">' + self.translate('cancel') + '</span>');

                $cancelButton.on('click', function (e) {
                    e.preventDefault();
                    $childBlock.remove();
                    $(document).scrollTo(self.$dynamicArea, 200);
                });

                $formAction.append($cancelButton);
            });

            // hook into the form submit event.
            $form.on('submit', function (e) {
                e.preventDefault();

                var blockName = $nameFormControl.val();
                if (blockName === '') {
                    blockName = event.blockType;
                }

                $.ajax({
                    url:  formAction + '&' + $.param({'composer': 1}),
                    data: $form.serialize(),
                    type: formMethod,
                    success: function (resp) {
                        if (resp.result && resp.result === 'ok' && resp.objectId) {
                            var createdEvent = $.Event('blockcreated');
                            createdEvent.$childBlock = $childBlock;
                            createdEvent.parentId    = event.containerId;
                            createdEvent.blockId     = resp.objectId;
                            createdEvent.blockName   = blockName;
                            createdEvent.blockType   = event.blockType;
                            $(self).trigger(createdEvent);
                        } else {
                            var loadedEvent = $.Event('blockcreateformloaded');
                            loadedEvent.response    = resp;
                            loadedEvent.containerId = event.containerId;
                            loadedEvent.blockType   = event.blockType;
                            loadedEvent.container   = $childBlock;
                            $(self).trigger(loadedEvent);

                            applyAdmin($childContent);
                        }
                    }
                });

                return false;
            });
        },

        /**
         * Toggle a child block using '--expanded' class check.
         *
         * @param $childBlock
         */
        toggleChildBlock: function ($childBlock) {
            var expandedClass = 'page-composer__container__child--expanded',
                $children     = this.$dynamicArea.find('.page-composer__container__child'),
                $childName    = $childBlock.find('.page-composer__container__child__name'),
                $nameInput    = $childName.find('.page-composer__container__child__name__input');

            if ($childBlock.hasClass(expandedClass)) {
                $childBlock.removeClass(expandedClass);
                if ($childName.has('.page-composer__container__child__name__input')) {
                    $childName.html($nameInput.val());
                }
            } else {
                $children.not($childBlock).removeClass(expandedClass);
                $childBlock.addClass(expandedClass);
            }
        },

        /**
         * Called when a block edit form has been loaded.
         *
         * @param event
         */
        handleBlockEditFormLoaded: function (event) {
            var self       = this,
                $title     = event.$block.find('.page-composer__container__child__edit h4'),
                $container = event.$block.find('.page-composer__container__child__content'),
                $loader    = event.$block.find('.page-composer__container__child__loader'),
                $form      = $container.find('form'),
                url        = $form.attr('action'),
                method     = $form.attr('method'),
                blockType  = event.$block.find('.page-composer__container__child__edit small').text().trim(),
                $nameFormControl,
                $positionFormControl;

            $form.find('input').each(function () {
                var $formControl    = $(this),
                    formControlName = $formControl.attr('name');

                if (self.isFormControlTypeByName(formControlName, 'name')) {
                    $nameFormControl = $formControl;

                    $title.html('<input type="text" class="page-composer__container__child__name__input" value="' + $title.text() + '">');
                    $input = $title.find('input');
                    $input.bind("propertychange keyup input paste", function (e) {
                        $nameFormControl.val($input.val());
                    });
                    $input.on('click', function (e) {
                        e.stopPropagation();
                        e.preventDefault();
                    });
                } else if (self.isFormControlTypeByName(formControlName, 'position')) {
                    $positionFormControl = $formControl;
                    $positionFormControl.closest('.form-group').hide();
                }
            });

            $form.on('submit', function (e) {
                e.preventDefault();

                $loader.show();

                $.ajax({
                    url:     url,
                    data:    $form.serialize(),
                    type:    method,
                    success: function (resp) {
                        $loader.hide();
                        if (resp.result && resp.result === 'ok') {
                            if (typeof $nameFormControl != 'undefined') {
                                $title.text($nameFormControl.val() !== '' ? $nameFormControl.val() : blockType);
                            }
                            event.$block.removeClass('page-composer__container__child--expanded');
                            $container.empty();
                        } else {
                            $container.html(resp);

                            var editFormEvent = $.Event('blockeditformloaded');
                            editFormEvent.$block = event.$block;
                            $(self).trigger(editFormEvent);

                            applyAdmin($container);
                        }
                    }
                });

                return false;
            });
        },

        /**
         * Takes control of a container child block.
         *
         * @param $childBlock
         */
        controlChildBlock: function ($childBlock) {
            var self           = this,
                $container     = $childBlock.find('.page-composer__container__child__content'),
                $loader        = $childBlock.find('.page-composer__container__child__loader'),
                $edit          = $childBlock.find('.page-composer__container__child__edit'),
                editUrl        = $edit.attr('href'),
                $remove        = $childBlock.find('.page-composer__container__child__remove'),
                $removeButton  = $remove.find('a'),
                $removeConfirm = $remove.find('.page-composer__container__child__remove__confirm'),
                $removeCancel  = $removeConfirm.find('.cancel'),
                $removeYes     = $removeConfirm.find('.yes'),
                removeUrl      = $removeButton.attr('href'),
                $switchEnabled = $childBlock.find('.page-composer__container__child__switch-enabled'),
                $switchLblEnbl = $switchEnabled.attr('data-label-enable'),
                $switchLblDsbl = $switchEnabled.attr('data-label-disable'),
                $switchButton  = $switchEnabled.find('a'),
                $switchBtnIcon = $switchButton.find('i'),
                $switchLabel   = $childBlock.find('.page-composer__container__child__enabled'),
                $switchLblSm   = $switchLabel.find('small'),
                $switchLblIcon = $switchLabel.find('i'),
                switchUrl      = $switchButton.attr('href'),
                enabled        = parseInt($childBlock.attr('data-parent-block-enabled'), 1);
                parentId       = parseInt($childBlock.attr('data-parent-block-id'), 10);

            $edit.click(function (e) {
                e.preventDefault();

                // edit form already loaded, just toggle
                if ($container.find('form').length > 0) {
                    self.toggleChildBlock($childBlock);
                    return;
                }

                // load edit form, then toggle
                $loader.show();
                $.ajax({
                    url:     editUrl,
                    success: function (resp) {
                        $container.html(resp);

                        var editFormEvent = $.Event('blockeditformloaded');
                        editFormEvent.$block = $childBlock;
                        $(self).trigger(editFormEvent);

                        applyAdmin($container);
                        $loader.hide();
                        self.toggleChildBlock($childBlock);
                    }
                });
            });

            $switchButton.on('click', function (e) {
                e.preventDefault();
                $.ajax({
                    url: switchUrl,
                    type: 'POST',
                    data: {
                        '_sonata_csrf_token': self.csrfTokens.switchEnabled,
                        'enabled': !enabled
                    },
                    success: function (resp) {
                        if (resp.status && resp.status === 'OK') {
                            $childBlock.attr('data-parent-block-enabled', !enabled);
                            enabled = !enabled;
                            $switchButton.toggleClass('bg-yellow bg-green');
                            $switchBtnIcon.toggleClass('fa-toggle-off fa-toggle-on');

                            if (enabled) {
                                $switchButton.html($switchLblDsbl);
                            } else {
                                $switchButton.html($switchLblEnbl);
                            }

                            $switchLblSm.toggleClass('bg-yellow bg-green');
                            $switchLblIcon.toggleClass('fa-times fa-check');

                            if ($childBlock.has('form')) {
                                var $form   = $childBlock.find('form'),
                                    $inputs = $form.find('input');

                                $inputs.each(function () {
                                    var $formControl    = $(this),
                                        formControlName = $formControl.attr('name');

                                    if (self.isFormControlTypeByName(formControlName, 'enabled')) {
                                        $formControl.val(parseInt(!enabled));
                                    }
                                });
                            }
                        } else {
                            self.containerNotification('an error occured while saving block enabled status', 'error', true);
                        }
                    },
                    error: function () {
                        self.containerNotification('an error occured while saving block enabled status', 'error', true);
                    }
                });
            });

            $removeButton.on('click', function (e) {
                e.preventDefault();
                $removeButton.hide();
                $removeConfirm.show();
            });

            $removeYes.on('click', function (e) {
                e.preventDefault();
                $.ajax({
                    url:  removeUrl,
                    type: 'POST',
                    data: {
                        '_method':            'DELETE',
                        '_sonata_csrf_token': self.csrfTokens.remove
                    },
                    success: function (resp) {
                        if (resp.result && resp.result === 'ok') {
                            $childBlock.remove();

                            var removedEvent = $.Event('blockremoved');
                            removedEvent.parentId = parentId;
                            $(self).trigger(removedEvent);
                        }
                    }
                });
            });

            $removeCancel.on('click', function (e) {
                e.preventDefault();
                $removeConfirm.hide();
                $removeButton.show();
            });
        },

        /**
         * Handler called when a container block has been loaded.
         *
         * @param event
         */
        handleContainerLoaded: function (event) {
            var self                     = this,
                $childrenContainer       = this.$dynamicArea.find('.page-composer__container__children'),
                $children                = this.$dynamicArea.find('.page-composer__container__child'),
                $blockTypeSelector       = this.$dynamicArea.find('.page-composer__block-type-selector'),
                $blockTypeSelectorLoader = $blockTypeSelector.find('.page-composer__block-type-selector__loader'),
                $blockTypeSelectorSelect = $blockTypeSelector.find('select'),
                $blockTypeSelectorButton = $blockTypeSelector.find('.page-composer__block-type-selector__confirm'),
                blockTypeSelectorUrl     = $blockTypeSelectorButton.attr('href');

            applyAdmin(this.$dynamicArea);

            // Load the block creation form trough ajax.
            $blockTypeSelectorButton.on('click', function (e) {
                e.preventDefault();

                $blockTypeSelectorLoader.css('display', 'inline-block');

                var blockType      = $blockTypeSelectorSelect.val(),
                    blockTypeLabel = $blockTypeSelectorSelect.find('option:selected').text();

                $.ajax({
                    url:     blockTypeSelectorUrl,
                    data:    {
                        type: blockType
                    },
                    success: function (resp) {
                        $blockTypeSelectorLoader.hide();

                        $(self).trigger($.Event('blockcreateformloaded', {
                            response:       resp,
                            containerId:    event.containerId,
                            blockType:      blockType,
                            blockTypeLabel: blockTypeLabel
                        }));
                    }
                });
            });

            // makes the container block children sortables.
            $childrenContainer.sortable({
                revert:         true,
                cursor:         'move',
                revertDuration: 200,
                delay:          200,
                helper: function (event, element) {
                    var $element = $(element),
                        name     = $element.find('.page-composer__container__child__edit h4').text().trim(),
                        type     = $element.find('.page-composer__container__child__edit small').text().trim();

                    $element.removeClass('page-composer__container__child--expanded');

                    return $('<div class="page-composer__container__child__helper">' +
                                 '<h4>' + name + '</h4>' +
                             '</div>');
                },
                update: function (event, ui) {
                    var newPositions = [];
                    $childrenContainer.find('.page-composer__container__child').each(function (position) {
                        var $child   = $(this),
                            parentId = $child.attr('data-parent-block-id'),
                            childId  = $child.attr('data-block-id');

                        // pending block creation has an undefined child id
                        if (typeof childId != 'undefined') {
                            newPositions.push({
                                'id':        parseInt(childId, 10),
                                'position':  position,
                                'parent_id': parseInt(parentId, 10),
                                'page_id':   self.pageId
                            });
                        }
                    });

                    if (newPositions.length > 0) {
                        var updateEvent = $.Event('blockpositionsupdate');
                        updateEvent.disposition = newPositions;
                        $(self).trigger(updateEvent);
                    }
                }
            });

            $children
                .each(function () {
                    self.controlChildBlock($(this));
                });
        },

        /**
         * Bind click handlers to template layout preview blocks.
         */
        bindPagePreviewHandlers: function () {
            var self = this;
            this.$containerPreviews
                .each(function () {
                    var $container = $(this);
                    $container.on('click', function (e) {
                        e.preventDefault();

                        var event = $.Event('containerclick');
                        event.$container = $container;
                        $(self).trigger(event);
                    });
                })
                .droppable({
                    hoverClass:        'hover',
                    tolerance:         'pointer',
                    revert:            true,
                    connectToSortable: '.page-composer__container__children',
                    drop: function (event, ui) {
                        var droppedBlockId = ui.draggable.attr('data-block-id');
                        if (typeof droppedBlockId != 'undefined') {
                            ui.helper.remove();

                            var $container     = $(this),
                                parentId       = parseInt(ui.draggable.attr('data-parent-block-id'), 10),
                                containerId    = parseInt($container.attr('data-block-id'), 10);
                                droppedBlockId = parseInt(droppedBlockId, 10);

                            if (parentId !== containerId) {
                                // play animation on drop, remove class on animation end to be able to re-apply
                                $container.addClass('dropped');
                                $container.on('webkitAnimationEnd oanimationend msAnimationEnd animationend', function (e) {
                                    $container.removeClass('dropped');
                                });

                                $.ajax({
                                    url: self.getRouteUrl('block_switch_parent'),
                                    data: {
                                        block_id:  droppedBlockId,
                                        parent_id: containerId
                                    },
                                    success: function (resp) {
                                        if (resp.result && resp.result === 'ok') {
                                            ui.draggable.remove();

                                            var switchedEvent = $.Event('blockparentswitched');
                                            switchedEvent.previousParentId = parentId;
                                            switchedEvent.newParentId      = containerId;
                                            switchedEvent.blockId          = droppedBlockId;
                                            $(self).trigger(switchedEvent);
                                        }
                                    }
                                });
                            }
                        }
                    }
                });

            if (this.$containerPreviews.length > 0) {
                this.loadContainer(this.$containerPreviews.eq(0));
            }
        },

        bindOrphansHandlers: function () {
            var self = this;
            this.$container.find('.page-composer__orphan-container').each(function () {
                var $container = $(this);
                $container.on('click', function (e) {
                    e.preventDefault();

                    var event = $.Event('containerclick');
                    event.$container = $container;
                    $(self).trigger(event);
                });
            });
        },

        /**
         * Loads the container detailed view trough ajax.
         *
         * @param $container
         */
        loadContainer: function ($container) {
            var url         = $container.attr('href'),
                containerId = $container.attr('data-block-id'),
                self        = this;

            this.$dynamicArea.empty();
            this.$containerPreviews.removeClass('active');
            this.$container.find('.page-composer__orphan-container').removeClass('active');

            $container.addClass('active');

            $.ajax({
                url:     url,
                success: function (resp) {
                    self.$dynamicArea.html(resp);

                    $(document).scrollTo(self.$dynamicArea, 200, {
                        offset: { top: -100 }
                    });

                    var event = $.Event('containerloaded');
                    event.containerId = containerId;
                    $(self).trigger(event);
                }
            });
        }
    };

    global.PageComposer = PageComposer;

})(jQuery, window);

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

;(function ( $, window, document, undefined ) {

    var pluginName = 'treeView',
        defaultRegistry = '.js-treeview',
        defaults = {
            togglersAttribute: '[data-treeview-toggler]',
            toggledState: 'is-toggled'
        };

    function TreeView( element, options ) {
        this.element = element;
        this.options = $.extend({}, defaults, options) ;
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
    }

    TreeView.prototype = {

        /**
         * Constructor
         */
        init: function() {
            this.setElements();
            this.setEvents();
        },

        /**
         * Cache DOM elements to limit DOM parsing
         */
        setElements: function() {
            this.$element = $(this.element);
            this.$togglers = this.$element.find(this.options.togglersAttribute);
        },

        /**
         * Set events and delegates
         */
        setEvents: function() {
            this.$togglers.on('click', $.proxy(this.toggle, this));
        },

        /**
         * Toggle an item
         */
        toggle: function(ev) {
            var $target = $(ev.currentTarget),
                $parent = $target.parent();
            $parent.toggleClass(this.options.toggledState);
            $parent.next('ul').slideToggle();
        }

    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new TreeView(this, options));
            }
        });
    };

    // Default standard registry
    $(function() {
        $(defaultRegistry)[pluginName]();
    });

})( jQuery, window, document );