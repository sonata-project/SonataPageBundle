module.exports = {
    source: './src',
    dest:   '../public',
    groups: {
        js: {
            front: ['js/page.js'],
            back:  ['js/composer.js', 'js/treeview.js']
        },
        css: {
            front: ['page.scss', 'default.scss'],
            back:  ['composer.scss', 'tree.scss']
        }
    }
};