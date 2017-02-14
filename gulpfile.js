var gulp = require('gulp');
var closure = require('gulp-closure-compiler-service');
var jsonlint = require("gulp-jsonlint");

var js_source = './interfaces/public/cashmusicjs/source/';
var paths = {
  scripts: [
      js_source+'cashmusic.js',
      js_source+'checkout/checkout.js',
      js_source+'share/share-buttons.js'],
    json: [
        './framework/settings/**/*.json',
        './framework/elements/**/*.json',
        './interfaces/admin/components/**/*.json'
    ]
};

gulp.task('compile', function() {
    return gulp.src(paths.scripts, {base: './interfaces/public/cashmusicjs/source/'})
        .pipe(closure())
        .pipe(gulp.dest('interfaces/public/'));
});

gulp.task('jsonlint', function() {
    return gulp.src(paths.json)
        .pipe(jsonlint())
        .pipe(jsonlint.reporter());
});

gulp.task('watch', function() {
    gulp.watch(paths.scripts, ['compile']);
    gulp.watch(paths.json, ['jsonlint']);
});

gulp.task('default', ['compile']);