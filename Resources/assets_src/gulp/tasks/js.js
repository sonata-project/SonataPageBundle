var gulp   = require('gulp');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var banner = require('../util/banner');
var rename = require('gulp-rename');
var config = require('../config');

gulp.task('js', function () {
    gulp.src(config.source + '/js/*.js')
        .pipe(concat('sonata-page.js'))
        .pipe(banner())
        .pipe(gulp.dest(config.dest))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(banner())
        .pipe(gulp.dest(config.dest));
});