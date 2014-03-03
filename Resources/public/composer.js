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
    var PageComposer = function () {
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
    };

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
        isFormControlTypeByName: function (name, type)
        {
            var position  = name.length,
                search    = '[' + type + ']',
                lastIndex = name.lastIndexOf(search);

            position = position - search.length;

            return lastIndex !== -1 && lastIndex === position;
        },

        handleCreatedChildBlock: function ($childBlock, containerId, blockId, blockName, blockType) {
            var content = this.renderTemplate('childBlock', {
                'name':     blockName,
                'type':     blockType,
                'edit_url': this.getRouteUrl('block_edit', { 'BLOCK_ID': blockId })
            });

            var $childBlock = $childBlock.html(content);
            this.controlChildBlock($childBlock);
        },

        /**
         * Handler called when block creation form is received.
         * Makes the form handled through ajax.
         *
         * @param containerId
         * @param blockType
         */
        onCreateBlockResponse: function (response, containerId, blockType) {
            var self               = this,
                $containerChildren = this.$dynamicArea.find('.page-composer__container__children'),
                $container         = this.$dynamicArea.find('.page-composer__container__main-edition-area');

            var $childBlock = $('<li class="page-composer__container__child"></li>');
            $childBlock.html(response)
            $containerChildren.append($childBlock);

            var $form         = $childBlock.find('form'),
                formAction    = $form.attr('action'),
                formMethod    = $form.attr('method'),
                $formControls = $form.find('input, select, textarea'),
                $formActions  = $form.find('.form-actions'),
                $nameFormControl,
                $parentFormControl,
                $positionFormControl;

            $(document).scrollTo($childBlock, 200);

            $form.parent().append('<span class="badge">' + blockType + '</span>');
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
                    $parentFormControl.val(containerId);
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
                    blockName = blockType;
                }

                $.ajax({
                    url:  formAction,
                    data: $form.serialize(),
                    type: formMethod,
                    success: function (resp) {
                        if (resp.result && resp.result === 'ok' && resp.objectId) {
                            self.handleCreatedChildBlock($childBlock, containerId, resp.objectId, blockName, blockType);
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

        removeChildBlock: function ($childBlock) {
            $childBlock.remove();
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
         * @param containerId
         */
        onContainerResponse: function (containerId) {
            var self                     = this,
                $children                = this.$dynamicArea.find('.page-composer__container__child'),
                $blockTypeSelector       = this.$dynamicArea.find('.page-composer__block-type-selector'),
                $blockTypeSelectorLoader = $blockTypeSelector.find('.page-composer__block-type-selector__loader'),
                $blockTypeSelectorSelect = $blockTypeSelector.find('select'),
                $blockTypeSelectorButton = $blockTypeSelector.find('.page-composer__block-type-selector__confirm'),
                blockTypeSelectorUrl     = $blockTypeSelectorButton.attr('href');

            // Load the block creation form trough ajax.
            $blockTypeSelectorButton.on('click', function (e) {
                e.preventDefault();

                $blockTypeSelectorLoader.css('display', 'inline-block');

                var blockType = $blockTypeSelectorSelect.val();
                $.ajax({
                    url:     blockTypeSelectorUrl + '?type=' + blockType,
                    success: function (resp) {
                        $blockTypeSelectorLoader.hide();
                        self.onCreateBlockResponse(resp, containerId, blockType);
                    }
                });
            });

            // makes the container block children sortables.
            this.$dynamicArea.find('.page-composer__container__children').sortable({
                revert:         true,
                cursor:         'move',
                revertDuration: 200,
                delay:          200,
                helper: function (event) {
                    return $( "<div class='ui-widget-header'>I'm a custom helper</div>" );
                }
            });

            $children.each(function () {
                self.controlChildBlock($(this));
            });
        },

        bindPagePreviewHandlers: function () {
            var self = this;
            this.$containerPreviews.each(function () {
                var $container = $(this);
                $container.on('click', function (e) {
                    e.preventDefault();
                    self.loadContainer($container);
                });
            });
            this.loadContainer(this.$containerPreviews.eq(0));
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
            this.$containerPreviews.removeClass('page-composer__page-preview__container--active');

            $container.addClass('page-composer__page-preview__container--active');

            $.ajax({
                url:     url,
                success: function (resp) {
                    self.$dynamicArea.html(resp);
                    self.onContainerResponse(containerId);
                }
            });
        }
    };

    global.PageComposer = PageComposer;

})(jQuery, window);