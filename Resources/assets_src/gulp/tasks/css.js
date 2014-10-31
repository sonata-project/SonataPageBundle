var gulp   = require('gulp');
var sass   = require('gulp-ruby-sass');
var cssmin = require('gulp-cssmin');
var rename = require('gulp-rename');
var banner = require('../util/banner');
var concat = require('gulp-concat');
var config = require('../config');

gulp.task('css', function () {
    gulp.src(config.source + '/scss/*.scss')
        .pipe(sass())
        .pipe(concat('sonata-page.css'))
        .pipe(banner())
        .pipe(gulp.dest(config.dest))
        .pipe(cssmin())
        .pipe(rename({ suffix: '.min' }))
        .pipe(banner())
        .pipe(gulp.dest(config.dest));
});