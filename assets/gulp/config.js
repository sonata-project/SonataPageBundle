module.exports = {
    source: './src',
    dest:   '../public',
    groups: {
        js: {
            back:  ['js/composer.js', 'js/treeview.js']
        },
        css: {
            front: ['default.scss'],
            back:  ['composer.scss', 'tree.scss']
        }
    }
};
