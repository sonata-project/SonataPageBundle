/**
 *
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * generated on: Fri Oct 31 2014 15:06:58 GMT+0100 (CET)
 * revision:     e97973344259d88e88d88078db248a0eb6198e32
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
    var PageComposer = function (pageId) {
        this.pageId             = pageId;
        this.$container         = $('.page-composer');
        this.$dynamicArea       = $('.page-composer__dyn-content');
        this.$pagePreview       = $('.page-composer__page-preview');
        this.$containerPreviews = this.$pagePreview.find('.page-composer__page-preview__container');
        this.routes             = {};
        this.csrfTokens         = {};
        this.templates          = {
            childBlock: '<a class="page-composer__container__child__edit" href="%edit_url%">' +
                    '<h4>%name%</h4>' +
                    '<small>%type%</small>' +
                    '<span class="page-composer__container__child__toggle">' +
                        '<span class="fa fa-chevron-down"></span>' +
                        '<span class="fa fa-chevron-up"></span>' +
                    '</span>' +
                '</a>' +
                '<div class="page-composer__container__child__right">' +
                    '<div class="page-composer__container__child__remove">' +
                        '<a class="badge" href="%remove_url%">Remove <i class="fa fa-times"></i> </a>' +
                        '<span class="page-composer__container__child__remove__confirm">' +
                            'Confirm Delete? <span class="yes">yes</span> <span class="cancel">cancel</span>' +
                        '</span>' +
                    '</div>' +

                    '<div class="page-composer__container__child__switch-enabled" data-label-enable="enable" data-label-disable="disable">' +
                        '<a class="badge bg-yellow" href="%switchenable_url%">Disable</a>' +
                    '</div>' +

                    '<div class="page-composer__container__child__enabled">' +
                        '<small class="badge bg-green"><i class="fa fa-check"></i></small>' +
                    '</div>' +
                '</div>' +
                '<div class="page-composer__container__child__content"></div>' +
                '<div class="page-composer__container__child__loader">' +
                    '<span>loading</span>' +
                '</div>'
        };

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
         * @param id
         * @param url
         */
        setRoute: function (id, url) {
            this.routes[id] = url;
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
         * @param id
         * @param parameters
         * @returns {*}
         */
        renderTemplate: function (id, parameters) {
            if (!this.templates[id]) {
                throw new Error('Template "' + id + '" does not exist');
            }

            var template = this.templates[id];
            for (var paramKey in parameters) {
                template = template.replace(new RegExp('%' + paramKey + '%'), parameters[paramKey]);
            }

            return template;
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

               var position = name.length,
               search = '[' + type + ']',
               lastIndex = name.lastIndexOf(search);
               position = position - search.length;

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
            var content = this.renderTemplate('childBlock', {
                'name':             event.blockName,
                'type':             event.blockType,
                'edit_url':         this.getRouteUrl('block_edit',          { 'BLOCK_ID': event.blockId }),
                'remove_url':       this.getRouteUrl('block_remove',        { 'BLOCK_ID': event.blockId }),
                'switchenable_url': this.getRouteUrl('block_switch_enable', { 'BLOCK_ID': event.blockId })
            });

            event.$childBlock.attr('data-block-id',        event.blockId);
            event.$childBlock.attr('data-parent-block-id', event.parentId);
            event.$childBlock.html(content);
            this.controlChildBlock(event.$childBlock);

            // refresh parent block child count
            var newChildCount = this.getContainerChildCountFromList(event.parentId);
            if (newChildCount !== null) {
                this.updateChildCount(event.parentId, newChildCount);
            }
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
                                '<input type="text" class="page-composer__container__child__name__input" />',
                            '</h4>',
                        '</a>',
                        '<div class="page-composer__container__child__right">',
                            '<span class="badge">' + event.blockType + '</span>',
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
                $formControls = $form.find('input, select, textarea'),
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
                    $cancelButton = $('<span class="btn btn-warning">cancel</span>');

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

                var blockType = $blockTypeSelectorSelect.val();
                $.ajax({
                    url:     blockTypeSelectorUrl,
                    data:    {
                        type: blockType
                    },
                    success: function (resp) {
                        $blockTypeSelectorLoader.hide();

                        var loadedEvent = $.Event('blockcreateformloaded');
                        loadedEvent.response    = resp;
                        loadedEvent.containerId = event.containerId;
                        loadedEvent.blockType   = blockType;
                        $(self).trigger(loadedEvent);
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

/**
 * Manages the Page Editor
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
var Sonata = Sonata || {};

Sonata.Page = {

    /**
     * Enable/disable debug mode
     *
     * @var boolean
     */
    debug: false,

    /**
     * Collection of blocks found on the page
     *
     * @var array
     */
    blocks: [],

    /**
     * Collection of containers found on the page
     *
     * @var array
     */
    containers: [],

    /**
     * block data
     *
     * @var array
     */
    data: [],

    /**
     * Block DOM selector
     *
     * @var string
     */
    blockSelector: '.cms-block',

    /**
     * Container DOM selector
     *
     * @var string
     */
    containerSelector: '.cms-container',

    /**
     * Drop placeholder CSS class
     *
     * @var string
     */
    dropPlaceHolderClass: 'cms-block-placeholder',

    /**
     * Drop placeholder size
     *
     * @var integer
     */
    dropPlaceHolderSize: 100,

    /**
     * Drop zone container CSS class
     *
     * @var string
     */
    dropZoneClass: 'cms-container-drop-zone',

    /**
     * Block hover CSS class
     *
     * @var string
     */
    blockHoverClass: 'cms-block-hand-over',

    /**
     * URLs to use when performing ajax operations
     *
     * @var Object
     */
    url: {
        block_save_position: null,
        block_edit: null
    },

    /**
     * Initialize Page editor mode
     */
    init: function(options) {
        options = options || [];
        for (property in options) {
            this[property] = options[property];
        }

        this.initInterface();
        this.initBlocks();
        this.initContainers();
        this.initBlockData();
    },

    /**
     * Initialize Admin interface (buttons)
     */
    initInterface: function() {
        jQuery('#page-action-enabled-edit').change(jQuery.proxy(this.toggleEditMode, this));
        jQuery('#page-action-save-position').click(jQuery.proxy(this.saveBlockLayout, this));
    },

    /**
     * Initialize block elements and behaviors
     */
    initBlocks: function() {
        // cache blocks
        this.blocks = jQuery(this.blockSelector);

        this.blocks.mouseover(jQuery.proxy(this.handleBlockHover, this));
        this.blocks.dblclick(jQuery.proxy(this.handleBlockClick, this));
    },

    /**
     * Initialize container elements and behaviors
     */
    initContainers: function() {
        // cache containers
        this.containers = jQuery(this.containerSelector);

        this.containers.sortable({
            connectWith:          this.containerSelector,
            items:                this.blockSelector,
            placeholder:          this.dropPlaceHolderClass,
            helper:               'clone',
            dropOnEmpty:          true,
            forcePlaceholderSize: this.dropPlaceHolderSize,
            opacity:              1,
            cursor:               'move',
            start:                jQuery.proxy(this.startContainerSort, this),
            stop:                 jQuery.proxy(this.stopContainerSort, this)
        }).sortable('disable');
    },

    /**
     * Initialize the block data (used to perform a diff when changing position/hierarchy)
     */
    initBlockData: function() {
        this.data = this.buildBlockData();
    },

    /**
     * Starts the container sorting
     *
     * @param event
     * @param ui
     */
    startContainerSort: function(event, ui) {
        this.containers.addClass(this.dropZoneClass);
        this.containers.append(jQuery('<div class="cms-fake-block">&nbsp;</div>'));
    },

    /**
     * Stops the container sorting
     *
     * @param event
     * @param ui
     */
    stopContainerSort: function(event, ui) {
        this.containers.removeClass(this.dropZoneClass);
        jQuery('div.cms-fake-block').remove();
        this.refreshLayers();
    },

    /**
     * Handle a click on the block
     *
     * @param event
     */
    handleBlockClick: function(event) {
        var target = event.currentTarget,
            id = jQuery(target).attr('data-id');

        window.open(this.url.block_edit.replace(/BLOCK_ID/, id), '_newtab');

        event.preventDefault();
        event.stopPropagation();
    },

    /**
     * Handle a hover on the block
     *
     * @param event
     */
    handleBlockHover: function(event) {
        this.blocks.removeClass(this.blockHoverClass);
        jQuery(this).addClass(this.blockHoverClass);
        event.stopPropagation();
    },

    /**
     * Toggle edit mode
     *
     * @param event
     */
    toggleEditMode: function(event) {
        if (event && event.currentTarget.checked) {
            jQuery('body').addClass('cms-edit-mode');
            jQuery('.cms-container').sortable('enable');
            this.buildLayers();
        } else {
            jQuery('body').removeClass('cms-edit-mode');
            jQuery('div.cms-container').sortable('disable');
            this.removeLayers();
        }

        event.preventDefault();
        event.stopPropagation();
    },

    /**
     * Build block layers
     */
    buildLayers:function() {
        this.blocks.each(function(index) {
            var block   = jQuery(this),
                role    = block.attr('data-role') || 'block',
                name    = block.attr('data-name') || 'missing data-name',
                id      = block.attr('data-id') || 'missing data-id',
                classes = [],
                layer;

            classes.push('cms-layout-layer');
            classes.push('cms-layout-role-'+role);

            // build layer
            layer = jQuery('<div class="'+classes.join(' ')+'" ></div>');
            layer.css({
                position: "absolute",
                left: 0,
                top: 0,
                width: '100%',
                height: '100%',
                zIndex: 2
            });

            // build layer title
            title = jQuery('<div class="cms-layout-title"></div>');
            title.css({
                position: "absolute",
                left: 0,
                top: 0,
                zIndex: 2
            });
            title.html('<span>'+name+'</span>');
            layer.append(title);

            block.prepend(layer);
        });
    },

    /**
     * Remove all block layers
     */
    removeLayers: function() {
        jQuery('.cms-layout-layer').remove();
    },

    /**
     * Refreshes the block layers
     */
    refreshLayers: function() {
        jQuery('.cms-layout-layer').each(function(position) {
            var layer = jQuery(this),
                block = layer.parent();

            layer.css('width', block.width());
            layer.css('height', block.height());
        });
    },

    /**
     * Build block data used to perform a database update of block position and hierarchy
     *
     * @return {Array} An array of block information with id, position, and parent id
     */
    buildBlockData: function() {
        var data = [];

        this.blocks.each(jQuery.proxy(function(index, block) {
            var item = this.buildSingleBlockData(block)
            if (item) {
                data.push(item);
            }
        }, this));

        // sort items on page, parent and position
        data.sort(function(a, b) {
            if (a.page_id == b.page_id) {
                if (a.parent_id == b.parent_id) {
                    return a.position - b.position;
                }
                return a.parent_id - b.parent_id;
            }
            return a.page_id - b.page_id;
        })

        return data;
    },

    /**
     * Builds a single block data
     *
     * @param original
     */
    buildSingleBlockData: function(original) {
        var block, id, parent, parentId, pageId, previous, position;

        block = jQuery(original);

        // retrieve current block id
        id = block.attr('data-id');
        if (!id) {
            this.log('Block has no data-id, ignored !');
            return;
        }

        // retrieve parent block container
        parent = this.findParentContainer(block);
        if (!parent) {
            this.log('Block '+id+' has no parent, it must be a root container, ignored');
            return;
        }
        parentId = jQuery(parent).attr('data-id');

        // retrieve root's page (because a root container cannot be moved)
        root = this.findRootContainer(block);
        if (!root) {
            this.log('Block '+id+' has no root but has a parent, should never happen!');
            return;
        }
        pageId = jQuery(root).attr('data-page-id');

        // get previous siblings to count position
        previous = block.prevAll(this.blockSelector+'[data-id]');
        position = previous.length + 1;

        if (!id || !parentId) {
            return;
        }

        return {
            id:        id,
            position:  position,
            parent_id: parentId,
            page_id:   pageId
        };
    },

    /**
     * Returns an array with differences from 2 arrays
     *
     * @param previousData Previous data
     * @param newData      New data
     *
     * @return Array
     */
    buildDiffBlockData: function(previousData, newData) {
        var diff = [];

        jQuery.map(previousData, function(previousItem, index) {
            var found;

            found = jQuery.grep(newData, function(newItem, index) {
                if (previousItem.id != newItem.id) {
                    return false;
                }

                if (previousItem.position != newItem.position || previousItem.parent_id != newItem.parent_id || previousItem.page_id != newItem.page_id) {
                    return true;
                }
            });

            if (found && found[0]) {
                diff.push(found[0]);
            }
        });

        return diff;
    },

    /**
     * Returns the parent container of a block
     *
     * @param block
     *
     * @return {*}
     */
    findParentContainer: function(block) {
        var parents, parent;

        parents = jQuery(block).parents(this.containerSelector+'[data-id]');
        parent = parents.get(0);

        return parent;
    },

    /**
     * Returns the root container of a block
     *
     * @param block
     *
     * @return {*}
     */
    findRootContainer: function(block) {
        var parents, root;

        parents = jQuery(block).parents(this.containerSelector+'[data-id]');
        root = parents.get(-1);

        return root;
    },

    /**
     * Save block layout to server
     *
     * @param event
     */
    saveBlockLayout: function(event) {
        var diff;

        event.preventDefault();
        event.stopPropagation();

        diff = this.buildDiffBlockData(this.data, this.buildBlockData());

        if (diff.length == 0) {
            alert('No changes found.');
            return;
        }

        jQuery.each(diff, jQuery.proxy(function(item, block) {
            this.log('Update block '+block.id+ ' (Page '+block.page_id+'), parent '+block.parent_id+', at position '+block.position+')');
        }, this));

        jQuery.ajax({
            type: 'POST',
            url: this.url.block_save_position,
            data: { disposition: diff },
            dataType: 'json',
            success: jQuery.proxy(function(data, status, xhr) {
                if (data.result == 'ok') {
                    alert('Block ordering saved!');
                    // re-initialize block data to consider as the new "previous" values
                    this.initBlockData();
                } else {
                    this.log(data);
                    alert('Server could not save block ordering!');
                }
            }, this),
            error: jQuery.proxy(function(xhr, status, error) {
                this.log('Unable to save block ordering: '+ error);
                this.log(status);
                this.log(xhr);
            }, this)
        });

    },

    /**
     * Log messages
     */
    log: function() {
        if (!this.debug) {
            return;
        }

        try {
            console.log(arguments);
        } catch(e) {

        }
    }
}
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