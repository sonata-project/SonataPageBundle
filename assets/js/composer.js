/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function PageComposer(pageId, options) {
  const settings = options || {};

  this.pageId = pageId;
  this.$container = jQuery('.page-composer');
  this.$dynamicArea = jQuery('.page-composer__dyn-content');
  this.$pagePreview = jQuery('.page-composer__page-preview');
  this.$containerPreviews = this.$pagePreview.find('.page-composer__page-preview__container');
  this.routes = jQuery.extend({}, settings.routes || {});
  this.translations = jQuery.extend({}, settings.translations || {});
  this.csrfTokens = jQuery.extend({}, settings.csrfTokens || {});

  this.bindPagePreviewHandlers();
  this.bindOrphansHandlers();

  // attach event listeners
  const self = this;
  const $this = jQuery(this);

  $this.on('containerclick', (e) => {
    self.loadContainer(e.$container);
  });
  $this.on('containerloaded', this.handleContainerLoaded);
  $this.on('blockcreated', this.handleBlockCreated);
  $this.on('blockremoved', this.handleBlockRemoved);
  $this.on('blockcreateformloaded', this.handleBlockCreateFormLoaded);
  $this.on('blockpositionsupdate', this.handleBlockPositionsUpdate);
  $this.on('blockeditformloaded', this.handleBlockEditFormLoaded);
  $this.on('blockparentswitched', this.handleBlockParentSwitched);
}

/**
 * Apply all Admin required functions.
 *
 * @param $context
 */
function applyAdmin($context) {
  if (typeof window.admin !== 'undefined') {
    return;
  }

  window.Admin.shared_setup($context);
}

PageComposer.prototype = {
  /**
   * Translates given label.
   *
   * @param {String} label
   * @return {String}
   */
  translate(label) {
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
  getRouteUrl(id, parameters) {
    if (!this.routes[id]) {
      throw new Error(`Route "${id}" does not exist`);
    }

    let url = this.routes[id];

    // eslint-disable-next-line no-restricted-syntax, guard-for-in
    for (const paramKey in parameters) {
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
  isFormControlTypeByName(name, type) {
    if (typeof name !== 'undefined') {
      let position = name.length;
      const search = `[${type}]`;
      const lastIndex = name.lastIndexOf(search);

      position -= search.length;

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
  handleBlockCreated(event) {
    const self = this;
    jQuery.ajax({
      url: this.getRouteUrl('block_preview', { BLOCK_ID: event.blockId }),
      type: 'GET',
      success(resp) {
        const $content = jQuery(resp);
        event.$childBlock.replaceWith($content);
        self.controlChildBlock($content);

        // refresh parent block child count
        const newChildCount = self.getContainerChildCountFromList(event.parentId);
        if (newChildCount !== null) {
          self.updateChildCount(event.parentId, newChildCount);
        }
      },
      error() {
        self.containerNotification('composer_preview_error', 'error', true);
      },
    });
  },

  /**
   * Remove given block.
   *
   * @param event
   */
  handleBlockRemoved(event) {
    // refresh parent block child count
    const newChildCount = this.getContainerChildCountFromList(event.parentId);
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
  containerNotification(message, type, persist) {
    const $notice = this.$dynamicArea.find('.page-composer__container__view__notice');
    if ($notice.length === 1) {
      if (this.containerNotificationTimer) {
        clearTimeout(this.containerNotificationTimer);
      }
      $notice.removeClass('persist success error');
      if (type) {
        $notice.addClass(type);
      }
      $notice.text(this.translate(message));
      $notice.show();
      if (persist !== true) {
        this.containerNotificationTimer = setTimeout(() => {
          $notice.hide().empty();
        }, 2000);
      } else {
        const $close = jQuery('<span class="close-notice">x</span>');
        $close.on('click', () => {
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
  handleBlockPositionsUpdate(event) {
    const self = this;
    this.containerNotification('composer_update_saving');
    jQuery.ajax({
      url: this.getRouteUrl('save_blocks_positions'),
      type: 'POST',
      data: { disposition: event.disposition },
      success(resp) {
        if (resp.result && resp.result === 'ok') {
          self.containerNotification('composer_update_saved', 'success');
        }
      },
      error() {
        self.containerNotification('composer_update_error', 'error', true);
      },
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
  handleBlockParentSwitched(event) {
    const $previousParentPreview = jQuery(`.block-preview-${event.previousParentId}`);
    const $oldChildCountIndicator = $previousParentPreview.find('.child-count');
    const oldChildCount = parseInt($oldChildCountIndicator.text().trim(), 10);
    const $newParentPreview = jQuery(`.block-preview-${event.newParentId}`);
    const $newChildCountIndicator = $newParentPreview.find('.child-count');
    const newChildCount = parseInt($newChildCountIndicator.text().trim(), 10);

    this.updateChildCount(event.previousParentId, oldChildCount - 1);
    this.updateChildCount(event.newParentId, newChildCount + 1);
  },

  /**
   * Compute child count for the given block container id.
   *
   * @param containerId
   * @returns {number}
   */
  getContainerChildCountFromList(containerId) {
    const $blockView = this.$dynamicArea.find(`.block-view-${containerId}`);

    if ($blockView.length === 0) {
      return null;
    }

    const $children = $blockView.find('.page-composer__container__child');
    let childCount = 0;

    $children.each(function child() {
      const $child = jQuery(this);
      const blockId = $child.attr('data-block-id');
      if (typeof blockId !== 'undefined') {
        childCount += 1;
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
  updateChildCount(blockId, count) {
    const $previewCount = jQuery(`.block-preview-${blockId}`);
    const $viewCount = jQuery(`.block-view-${blockId}`);

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
  handleBlockCreateFormLoaded(event) {
    const self = this;
    const $containerChildren = this.$dynamicArea.find('.page-composer__container__children');
    const $container = this.$dynamicArea.find('.page-composer__container__main-edition-area');
    let $childBlock = null;
    let $childContent = null;

    if (event.container) {
      $childBlock = event.container;
      $childContent = $childBlock.find('.page-composer__container__child__content');

      $childContent.html(event.response);
    } else {
      $childBlock = jQuery(
        [
          '<li class="page-composer__container__child">',
          '<a class="page-composer__container__child__edit">',
          '<h4 class="page-composer__container__child__name">',
          '<input type="text" class="page-composer__container__child__name__input">',
          '</h4>',
          '</a>',
          '<div class="page-composer__container__child__right">',
          `<span class="badge">${event.blockTypeLabel}</span>`,
          '</div>',
          '<div class="page-composer__container__child__content">',
          '</div>',
          '</li>',
        ].join('')
      );
      $childContent = $childBlock.find('.page-composer__container__child__content');

      $childContent.append(event.response);
      $containerChildren.append($childBlock);

      $childContent.show();
    }

    const $form = $childBlock.find('form');
    const formAction = $form.attr('action');
    const formMethod = $form.attr('method');
    const $formControls = $form.find('input, select, textarea');
    const $formActions = $form.find('.form-actions');
    const $childName = this.$dynamicArea.find('.page-composer__container__child__name');
    let $nameFormControl;
    let $parentFormControl;
    let $positionFormControl;

    applyAdmin($form);

    jQuery(document).scrollTo($childBlock, 200);

    $container.show();

    // scan form elements to find name/parent/position,
    // then set value according to current container and hide it.
    $formControls.each(function formControl() {
      const $formControl = jQuery(this);
      const formControlName = $formControl.attr('name');

      if (self.isFormControlTypeByName(formControlName, 'name')) {
        $nameFormControl = $formControl;
        $childName
          .find('.page-composer__container__child__name__input')
          .on('propertychange keyup input paste', function onChange() {
            $nameFormControl.val(jQuery(this).val());
          });
      } else if (self.isFormControlTypeByName(formControlName, 'parent')) {
        $parentFormControl = $formControl;
        $parentFormControl.val(event.containerId);
        $parentFormControl.parent().parent().hide();
      } else if (self.isFormControlTypeByName(formControlName, 'position')) {
        $positionFormControl = $formControl;
        $positionFormControl.val($containerChildren.find('> *').length - 1);
        $positionFormControl.closest('.form-group').hide();
      }
    });

    $formActions.each(function formActionItem() {
      const $formAction = jQuery(this);
      const $cancelButton = jQuery(
        `<span class="btn btn-warning">${self.translate('cancel')}</span>`
      );

      $cancelButton.on('click', (e) => {
        e.preventDefault();
        $childBlock.remove();
        jQuery(document).scrollTo(self.$dynamicArea, 200);
      });

      $formAction.append($cancelButton);
    });

    // hook into the form submit event.
    $form.on('submit', (e) => {
      e.preventDefault();

      let blockName = $nameFormControl.val();
      if (blockName === '') {
        blockName = event.blockType;
      }

      jQuery.ajax({
        url: `${formAction}&${jQuery.param({ composer: 1 })}`,
        data: $form.serialize(),
        type: formMethod,
        headers: {
          Accept: 'text/html, application/xhtml+xml;',
        },
        success(resp) {
          if (resp.result && resp.result === 'ok' && resp.objectId) {
            const createdEvent = jQuery.Event('blockcreated');
            createdEvent.$childBlock = $childBlock;
            createdEvent.parentId = event.containerId;
            createdEvent.blockId = resp.objectId;
            createdEvent.blockName = blockName;
            createdEvent.blockType = event.blockType;
            jQuery(self).trigger(createdEvent);
          } else {
            const loadedEvent = jQuery.Event('blockcreateformloaded');
            loadedEvent.response = resp;
            loadedEvent.containerId = event.containerId;
            loadedEvent.blockType = event.blockType;
            loadedEvent.container = $childBlock;
            jQuery(self).trigger(loadedEvent);

            applyAdmin($childContent);
          }
        },
      });

      return false;
    });
  },

  /**
   * Toggle a child block using '--expanded' class check.
   *
   * @param $childBlock
   */
  toggleChildBlock($childBlock) {
    const expandedClass = 'page-composer__container__child--expanded';
    const $children = this.$dynamicArea.find('.page-composer__container__child');
    const $childName = $childBlock.find('.page-composer__container__child__name');
    const $nameInput = $childName.find('.page-composer__container__child__name__input');

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
  handleBlockEditFormLoaded(event) {
    const self = this;
    const $title = event.$block.find('.page-composer__container__child__edit h4');
    const $container = event.$block.find('.page-composer__container__child__content');
    const $loader = event.$block.find('.page-composer__container__child__loader');
    const $form = $container.find('form');
    const url = $form.attr('action');
    const method = $form.attr('method');
    const blockType = event.$block
      .find('.page-composer__container__child__edit small')
      .text()
      .trim();
    let $nameFormControl;
    let $positionFormControl;

    $form.find('input').each(function input() {
      const $formControl = jQuery(this);
      const formControlName = $formControl.attr('name');

      if (self.isFormControlTypeByName(formControlName, 'name')) {
        $nameFormControl = $formControl;
        $title.html(
          `<input type="text" class="page-composer__container__child__name__input" value="${$title
            .text()
            .trim()}">`
        );
        const $input = $title.find('input');
        $input.bind('propertychange keyup input paste', () => {
          $nameFormControl.val($input.val());
        });
        $input.on('click', (e) => {
          e.stopPropagation();
          e.preventDefault();
        });
      } else if (self.isFormControlTypeByName(formControlName, 'position')) {
        $positionFormControl = $formControl;
        $positionFormControl.closest('.form-group').hide();
      }
    });

    $form.on('submit', (e) => {
      e.preventDefault();

      $loader.show();

      jQuery.ajax({
        url,
        data: $form.serialize(),
        type: method,
        headers: {
          Accept: 'text/html, application/xhtml+xml;',
        },
        success(resp) {
          $loader.hide();
          if (resp.result && resp.result === 'ok') {
            if (typeof $nameFormControl !== 'undefined') {
              $title.text($nameFormControl.val() !== '' ? $nameFormControl.val() : blockType);
            }
            event.$block.removeClass('page-composer__container__child--expanded');
            $container.empty();
          } else {
            $container.html(resp);

            const editFormEvent = jQuery.Event('blockeditformloaded');
            editFormEvent.$block = event.$block;
            jQuery(self).trigger(editFormEvent);

            applyAdmin($container);
          }
        },
      });

      return false;
    });
  },

  /**
   * Takes control of a container child block.
   *
   * @param $childBlock
   */
  controlChildBlock($childBlock) {
    const self = this;
    const $container = $childBlock.find('.page-composer__container__child__content');
    const $loader = $childBlock.find('.page-composer__container__child__loader');
    const $edit = $childBlock.find('.page-composer__container__child__edit');
    const editUrl = $edit.attr('href');
    const $remove = $childBlock.find('.page-composer__container__child__remove');
    const $removeButton = $remove.find('a');
    const $switchEnabled = $childBlock.find('.page-composer__container__child__switch-enabled');
    const $switchLblEnbl = $switchEnabled.attr('data-label-enable');
    const $switchLblDsbl = $switchEnabled.attr('data-label-disable');
    const $switchButton = $switchEnabled.find('a');
    const $switchBtnIcon = $switchButton.find('i');
    const $switchLabel = $childBlock.find('.page-composer__container__child__enabled');
    const $switchLblSm = $switchLabel.find('small');
    const $switchLblIcon = $switchLabel.find('i');
    const switchUrl = $switchButton.attr('href');
    let enabled = parseInt($childBlock.attr('data-block-enabled'), 2);

    $edit.click((e) => {
      e.preventDefault();

      // edit form already loaded, just toggle
      if ($container.find('form').length > 0) {
        self.toggleChildBlock($childBlock);
        return;
      }

      // load edit form, then toggle
      $loader.show();
      jQuery.ajax({
        url: editUrl,
        success(resp) {
          $container.html(resp);

          const editFormEvent = jQuery.Event('blockeditformloaded');
          editFormEvent.$block = $childBlock;
          jQuery(self).trigger(editFormEvent);

          applyAdmin($container);
          $loader.hide();
          self.toggleChildBlock($childBlock);
        },
      });
    });

    $switchButton.on('click', (e) => {
      e.preventDefault();
      jQuery.ajax({
        url: switchUrl,
        type: 'POST',
        data: {
          _sonata_csrf_token: self.csrfTokens.switchEnabled,
          value: !enabled,
        },
        success() {
          $childBlock.attr('data-block-enabled', enabled ? '0' : '1');
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
            const $form = $childBlock.find('form');
            const $inputs = $form.find('input');

            $inputs.each(function input() {
              const $formControl = jQuery(this);
              const formControlName = $formControl.attr('name');

              if (self.isFormControlTypeByName(formControlName, 'enabled')) {
                $formControl.val(parseInt(!enabled, 10));
              }
            });
          }
        },
        error() {
          self.containerNotification('composer_status_error', 'error', true);
        },
      });
    });

    $removeButton.on('click', (e) => {
      e.preventDefault();
      self.confirmRemoveContainer($childBlock);
    });
  },

  /**
   * Shows a confirm dialog for a child removal request
   *
   * @param $childBlock
   */
  confirmRemoveContainer($childBlock) {
    const self = this;
    const $remove = $childBlock.find('.page-composer__container__child__remove');
    const $removeButton = $remove.find('a');
    let $removeDialog = $childBlock.find('.page-composer__container__child__remove__dialog');
    const removeUrl = $removeButton.attr('href');
    const parentId = parseInt($childBlock.attr('data-parent-block-id'), 10);

    if ($removeDialog.length === 0) {
      $removeDialog = jQuery(
        [
          '<div class="modal fade page-composer__container__child__remove__dialog" tabindex="-1" role="dialog">',
          '<div class="modal-dialog" role="document">',
          '<div class="modal-content">',
          '<div class="modal-header">',
          '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>',
          `<h4 class="modal-title">${this.translate('composer_remove_confirm')}</h4>`,
          '</div>',
          '<div class="modal-body">',
          '</div>',
          '<div class="modal-footer">',
          `<button type="button" class="btn btn-default" data-dismiss="modal">${this.translate(
            'cancel'
          )}</button>`,
          `<button type="button" class="btn btn-primary">${this.translate('yes')}</button>`,
          '</div>',
          '</div>',
          '</div>',
          '</div>',
        ].join('')
      );
      $childBlock.append($removeDialog);
    }

    const $removeYes = $removeDialog.find('.btn-primary');

    $removeYes.on('click', () => {
      jQuery.ajax({
        url: removeUrl,
        type: 'POST',
        data: {
          _method: 'DELETE',
          _sonata_csrf_token: self.csrfTokens.remove,
        },
        success(resp) {
          if (resp.result && resp.result === 'ok') {
            $childBlock.remove();

            const removedEvent = jQuery.Event('blockremoved');
            removedEvent.parentId = parentId;
            jQuery(self).trigger(removedEvent);
          }
        },
      });
      $removeDialog.modal('hide');

      // Sometimes, there is a bug and the modal backdrop didn't fade out, so we have to force it
      if (jQuery('.modal-backdrop').length !== 0) {
        jQuery('.modal-backdrop').hide();
      }
    });

    $removeDialog.modal('show');
  },

  /**
   * Handler called when a container block has been loaded.
   *
   * @param event
   */
  handleContainerLoaded(event) {
    const self = this;
    const $childrenContainer = this.$dynamicArea.find('.page-composer__container__children');
    const $children = this.$dynamicArea.find('.page-composer__container__child');
    const $blockTypeSelector = this.$dynamicArea.find('.page-composer__block-type-selector');
    const $blockTypeSelectorLoader = $blockTypeSelector.find(
      '.page-composer__block-type-selector__loader'
    );
    const $blockTypeSelectorSelect = $blockTypeSelector.find('select');
    const $blockTypeSelectorButton = $blockTypeSelector.find(
      '.page-composer__block-type-selector__confirm'
    );
    const blockTypeSelectorUrl = $blockTypeSelectorButton.attr('href');

    applyAdmin(this.$dynamicArea);

    // Load the block creation form trough ajax.
    $blockTypeSelectorButton.on('click', (e) => {
      e.preventDefault();

      $blockTypeSelectorLoader.css('display', 'inline-block');

      const blockType = $blockTypeSelectorSelect.val();
      const blockTypeLabel = $blockTypeSelectorSelect.find('option:selected').text().trim();

      jQuery.ajax({
        url: blockTypeSelectorUrl,
        data: {
          type: blockType,
        },
        success(resp) {
          $blockTypeSelectorLoader.hide();

          jQuery(self).trigger(
            jQuery.Event('blockcreateformloaded', {
              response: resp,
              containerId: event.containerId,
              blockType,
              blockTypeLabel,
            })
          );
        },
      });
    });

    // makes the container block children sortables.
    $childrenContainer.sortable({
      revert: true,
      cursor: 'move',
      revertDuration: 200,
      delay: 200,
      helper(_, element) {
        const $element = jQuery(element);
        const name = $element.find('.page-composer__container__child__edit h4').text().trim();

        $element.removeClass('page-composer__container__child--expanded');

        return jQuery(
          `<div class="page-composer__container__child__helper"><h4>${name}</h4></div>`
        );
      },
      update() {
        const newPositions = [];
        $childrenContainer
          .find('.page-composer__container__child')
          .each(function containerChild(position) {
            const $child = jQuery(this);
            const parentId = $child.attr('data-parent-block-id');
            const childId = $child.attr('data-block-id');

            // pending block creation has an undefined child id
            if (typeof childId !== 'undefined') {
              newPositions.push({
                id: parseInt(childId, 10),
                position,
                parent_id: parseInt(parentId, 10),
                page_id: self.pageId,
              });
            }
          });

        if (newPositions.length > 0) {
          const updateEvent = jQuery.Event('blockpositionsupdate');
          updateEvent.disposition = newPositions;
          jQuery(self).trigger(updateEvent);
        }
      },
    });

    $children.each(function child() {
      self.controlChildBlock(jQuery(this));
    });
  },

  /**
   * Bind click handlers to template layout preview blocks.
   */
  bindPagePreviewHandlers() {
    const self = this;
    this.$containerPreviews
      .each(function containerPreview() {
        const $container = jQuery(this);
        $container.on('click', (e) => {
          e.preventDefault();

          const event = jQuery.Event('containerclick');
          event.$container = $container;
          jQuery(self).trigger(event);
        });
      })
      .droppable({
        hoverClass: 'hover',
        tolerance: 'pointer',
        revert: true,
        connectToSortable: '.page-composer__container__children',
        accept(source) {
          // NEXT_MAJOR: Remove the 'data-block-whitelist'
          let blockAllowlist =
            jQuery(this).attr('data-block-allowlist') || jQuery(this).attr('data-block-whitelist');
          if (blockAllowlist === '') {
            return true;
          }

          blockAllowlist = blockAllowlist.split(',');
          const sourceBlockType = jQuery(source).attr('data-block-type');

          return blockAllowlist.indexOf(sourceBlockType) !== -1;
        },
        drop(event, ui) {
          let droppedBlockId = ui.draggable.attr('data-block-id');

          if (typeof droppedBlockId !== 'undefined') {
            ui.helper.remove();

            const $container = jQuery(this);
            const parentId = parseInt(ui.draggable.attr('data-parent-block-id'), 10);
            const containerId = parseInt($container.attr('data-block-id'), 10);
            droppedBlockId = parseInt(droppedBlockId, 10);

            if (parentId !== containerId) {
              // play animation on drop, remove class on animation end to be able to re-apply
              $container.addClass('dropped');
              $container.on('webkitAnimationEnd oanimationend msAnimationEnd animationend', () => {
                $container.removeClass('dropped');
              });

              jQuery.ajax({
                url: self.getRouteUrl('block_switch_parent'),
                data: {
                  block_id: droppedBlockId,
                  parent_id: containerId,
                },
                success(resp) {
                  if (resp.result && resp.result === 'ok') {
                    ui.draggable.remove();

                    const switchedEvent = jQuery.Event('blockparentswitched');
                    switchedEvent.previousParentId = parentId;
                    switchedEvent.newParentId = containerId;
                    switchedEvent.blockId = droppedBlockId;
                    jQuery(self).trigger(switchedEvent);
                  }
                },
              });
            }
          }
        },
      });

    if (this.$containerPreviews.length > 0) {
      this.loadContainer(this.$containerPreviews.eq(0));
    }
  },

  bindOrphansHandlers() {
    const self = this;
    this.$container.find('.page-composer__orphan-container').each(function orphanContainer() {
      const $container = jQuery(this);
      $container.on('click', (e) => {
        e.preventDefault();

        const event = jQuery.Event('containerclick');
        event.$container = $container;
        jQuery(self).trigger(event);
      });
    });
  },

  /**
   * Loads the container detailed view trough ajax.
   *
   * @param $container
   */
  loadContainer($container) {
    const url = $container.attr('href');
    const containerId = $container.attr('data-block-id');
    const self = this;

    this.$dynamicArea.empty();
    this.$containerPreviews.removeClass('active');
    this.$container.find('.page-composer__orphan-container').removeClass('active');

    $container.addClass('active');

    jQuery.ajax({
      url,
      success(resp) {
        self.$dynamicArea.html(resp);

        jQuery(document).scrollTo(self.$dynamicArea, 200, {
          offset: { top: -100 },
        });

        const event = jQuery.Event('containerloaded');
        event.containerId = containerId;
        jQuery(self).trigger(event);
      },
    });
  },
};

window.PageComposer = PageComposer;

// auto-initialize plugin
jQuery(() => {
  jQuery('[data-page-composer]').each(function pageComposer() {
    const attr = jQuery(this).data('page-composer');
    PageComposer(attr.pageId, attr);
  });
});
