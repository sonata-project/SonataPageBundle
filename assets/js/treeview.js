/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const pluginName = 'treeView';
const defaults = {
  togglersAttribute: '[data-treeview-toggler]',
  toggledState: 'is-toggled',
};

function TreeView(element, options) {
  this.element = element;
  this.options = jQuery.extend({}, defaults, options);
  this.defaults = defaults;
  this.name = pluginName;
  this.init();
}

TreeView.prototype = {
  /**
   * Constructor
   */
  init() {
    this.setElements();
    this.setEvents();
  },

  /**
   * Cache DOM elements to limit DOM parsing
   */
  setElements() {
    this.$element = jQuery(this.element);
    this.$togglers = this.$element.find(this.options.togglersAttribute);
  },

  /**
   * Set events and delegates
   */
  setEvents() {
    this.$togglers.on('click', this.toggle.bind(this));
  },

  /**
   * Toggle an item
   */
  toggle(ev) {
    const $target = jQuery(ev.currentTarget);
    const $parent = $target.parent();

    $parent.toggleClass(this.options.toggledState);
    $parent.next('ul').slideToggle();
  },
};

// A really lightweight plugin wrapper around the constructor,
// preventing against multiple instantiations
jQuery.fn[pluginName] = function plugin(options) {
  return this.each(function plugins() {
    if (!jQuery.data(this, `plugin_${pluginName}`)) {
      jQuery.data(this, `plugin_${pluginName}`, new TreeView(this, options));
    }
  });
};
