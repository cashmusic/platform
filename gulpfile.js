var gulp = require('gulp');
var closure = require('gulp-closure-compiler-service');
var jsonlint = require("gulp-jsonlint");
var shell = require('gulp-shell');
var argv = require('yargs').argv;
var rename = require("gulp-rename");
var uglify = require('gulp-uglify');

const debug = require('gulp-debug');


var js_source = './interfaces/public/cashmusicjs/source/';
var paths = {
  scripts: [
      js_source+'cashmusic.js',js_source+'checkout/checkout.js'], /*,
     ,
     js_source+'share/share-buttons.js'*/
    json: [
        './framework/settings/**/*.json',
        './framework/elements/**/*.json',
        './interfaces/admin/components/**/*.json'
    ]
};

gulp.task('compile', function() {

    return gulp.src(paths.scripts, {base: './interfaces/public/cashmusicjs/source/'})
        .pipe(debug({title: 'Compile JS:'}))
        .pipe(closure())
        .pipe(gulp.dest('interfaces/public/'));
});

gulp.task('compile-admin', function() {
    return gulp.src(paths.scripts, {base: './interfaces/public/cashmusicjs/source/'})
        .pipe(debug({title: 'Compile Admin JS:'}))
        .pipe(closure())
        .pipe(gulp.dest('interfaces/public/'));
});

gulp.task('compile-admin-js', function() {
    return gulp.src(['./interfaces/admin/ui/default/assets/scripts/jquery.admin.full.js'])
        .pipe(debug({title: 'Compile Admin Interface JS:'}))
        .pipe(closure())
        .pipe(rename("jquery.admin.js"))
        .pipe(gulp.dest('./interfaces/admin/ui/default/assets/scripts/'));
});

gulp.task('jsonlint', function() {
    return gulp.src(paths.json)
        .pipe(debug({title: 'JSON Linter:'}))
        .pipe(jsonlint())
        .pipe(jsonlint.reporter());
});

gulp.task('vagrant-rsync', shell.task([
    'vagrant rsync '+argv.box
]));

gulp.task('watch', function() {
    gulp.watch(paths.scripts, ['compile']);
    gulp.watch(paths.json, ['jsonlint']);
});

gulp.task('watch-rsync', function() {
    gulp.watch("./**/*.*", ['vagrant-rsync']);
});

gulp.task('default', ['compile']);