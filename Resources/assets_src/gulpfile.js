/*
    gulpfile.js
    ===========
    Rather than manage one giant configuration file responsible
    for creating multiple tasks, each task has been broken out into
    its own file in gulp/tasks. Any file in that folder gets automatically
    required by the loop in ./gulp/index.js (required below).

    To add a new task, simply add a new task file to gulp/tasks.
*/

var gutil = require('gulp-util');

var banner = [
"                              __",
"      _________  ____  ____ _/ /_____ _",
"     / ___/ __ \\/ __ \\/ __ `/ __/ __ `/",
"    (__  ) /_/ / / / / /_/ / /_/ /_/ /",
"   /____/\\____/_/ /_/\\__,_/\\__/\\__,_/",
"                         PAGE BUNDLE",
""
].join('\n');

console.log(banner);

require('./gulp');