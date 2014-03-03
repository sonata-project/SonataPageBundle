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
                    '<h4>%name%' +
                        '<span class="page-composer__container__child__toggle">' +
                            '<span class="icon-chevron-down"></span>' +
                            '<span class="icon-chevron-up"></span>' +
                        '</span>' +
                    '</h4>' +
                    '<small>%type%</small>' +
                '</a>' +
                '<div class="page-composer__container__child__remove">' +
                    '<a class="badge" href="%remove_url%">remove</a>' +
                    '<span class="page-composer__container__child__remove__confirm">' +
                    'confirm delete ? <span class="yes">yes</span> <span class="cancel">cancel</span>' +
                    '</span>' +
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
        $this.on('blockcreateformloaded', this.handleBlockCreateFormLoaded);
        $this.on('blockpositionsupdate',  this.handleBlockPositionsUpdate);
        $this.on('blockeditformloaded',   this.handleBlockEditFormLoaded);
    };

    /**
     * Apply all Admin required functions.
     *
     * @param $context
     */
    function applyAdmin($context) {
        if (global.admin === 'undefined') {
            return;
        }

        Admin.setup_select2($context);
        Admin.setup_xeditable($context);
        Admin.add_pretty_errors($context);
        Admin.add_filters($context);
        Admin.set_object_field_value($context);
        Admin.setup_collection_buttons($context);
        Admin.setup_per_page_switcher($context);
        Admin.setup_form_tabs_for_errors($context);
        Admin.setup_inline_form_errors($context);
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
            var position  = name.length,
                search    = '[' + type + ']',
                lastIndex = name.lastIndexOf(search);

            position = position - search.length;

            return lastIndex !== -1 && lastIndex === position;
        },

        /**
         * Called when a child block has been created.
         * The event has the following properties:
         *
         *    $childBlock
         *    parentId
         *    blockId
         *    blockName
         *    blockType
         *
         * @param event
         */
        handleBlockCreated: function (event) {
            var content = this.renderTemplate('childBlock', {
                'name':       event.blockName,
                'type':       event.blockType,
                'edit_url':   this.getRouteUrl('block_edit',   { 'BLOCK_ID': event.blockId }),
                'remove_url': this.getRouteUrl('block_remove', { 'BLOCK_ID': event.blockId })
            });

            event.$childBlock.attr('data-block-id',        event.blockId);
            event.$childBlock.attr('data-parent-block-id', event.parentId);
            event.$childBlock.html(content);
            this.controlChildBlock(event.$childBlock);
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
         * Save block positions. event.disposition contains positions data:
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

            var $childBlock = $('<li class="page-composer__container__child"></li>');
            $childBlock.html(event.response);
            $containerChildren.append($childBlock);

            var $form         = $childBlock.find('form'),
                formAction    = $form.attr('action'),
                formMethod    = $form.attr('method'),
                $formControls = $form.find('input, select, textarea'),
                $formActions  = $form.find('.form-actions'),
                $nameFormControl,
                $parentFormControl,
                $positionFormControl;

            applyAdmin($form);

            $(document).scrollTo($childBlock, 200);

            $form.parent().append('<span class="badge">' + event.blockType + '</span>');
            $container.show();

            // scan form elements to find name/parent/position,
            // then set value according to current container and hide it.
            $formControls.each(function () {
                var $formControl    = $(this),
                    formControlName = $formControl.attr('name');

                if (self.isFormControlTypeByName(formControlName, 'name')) {
                    $nameFormControl = $formControl;
                } else if (self.isFormControlTypeByName(formControlName, 'parent')) {
                    $parentFormControl = $formControl;
                    $parentFormControl.val(event.containerId);
                    $parentFormControl.parent().parent().hide();
                } else if (self.isFormControlTypeByName(formControlName, 'position')) {
                    $positionFormControl = $formControl;
                    $positionFormControl.val($containerChildren.find('> *').length);
                    $positionFormControl.parent().parent().parent().hide();
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
                    url:  formAction,
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
                        }
                    }
                });

                return false;
            });
        },

        toggleChildBlock: function ($childBlock) {
            var expandedClass = 'page-composer__container__child--expanded',
                $children     = this.$dynamicArea.find('.page-composer__container__child');

            if ($childBlock.hasClass(expandedClass)) {
                $childBlock.removeClass(expandedClass);
            } else {
                $children.not($childBlock).removeClass(expandedClass);
                $childBlock.addClass(expandedClass);
            }
        },

        /**
         * Remove givent block.
         *
         * @param $childBlock
         */
        removeChildBlock: function ($childBlock) {
            $childBlock.remove();
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
                } else if (self.isFormControlTypeByName(formControlName, 'position')) {
                    $positionFormControl = $formControl;
                    $positionFormControl.parent().parent().parent().hide();
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
                            if ($nameFormControl !== 'undefined') {
                                $title.text($nameFormControl.val() !== '' ? $nameFormControl.val() : blockType);
                            }
                            event.$block.removeClass('page-composer__container__child--expanded');
                            $container.empty();
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
                removeUrl      = $removeButton.attr('href');

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
                            self.removeChildBlock($childBlock);
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
                    url:     blockTypeSelectorUrl + '?type=' + blockType,
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
                        if (childId !== 'undefined') {
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
            this.$containerPreviews.each(function () {
                var $container = $(this);
                $container.on('click', function (e) {
                    e.preventDefault();

                    var event = $.Event('containerclick');
                    event.$container = $container;
                    $(self).trigger(event);
                });
            })
            .droppable({
                hoverClass: 'hover',
                tolerance:  'pointer',
                drop: function (event, ui) {
                    console.log(ui.draggable);
                }
            });
            this.loadContainer(this.$containerPreviews.eq(0));
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