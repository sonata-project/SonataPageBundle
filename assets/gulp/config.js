module.exports = {
    source: './assets/src',
    dest:   './src/Resources/public',
    groups: {
        js: {
            back:  ['js/droppable.js', 'js/composer.js', 'js/treeview.js']
        },
        css: {
            front: ['default.scss'],
            back:  ['composer.scss', 'tree.scss']
        }
    }
};
