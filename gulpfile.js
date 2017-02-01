var gulp = require('gulp');
var closure = require('gulp-closure-compiler-service');

var source_path = './interfaces/public/cashmusicjs/source/';
var paths = {
  scripts: [
      source_path+'cashmusic.js',
      source_path+'checkout/checkout.js']
};

gulp.task('compile', function() {
    return gulp.src(paths.scripts, {base: './interfaces/public/cashmusicjs/source/'})
        .pipe(closure())
        .pipe(gulp.dest('interfaces/public/'));
});

gulp.task('watch', function() {
    gulp.watch(paths.scripts, ['compile']);
});

gulp.task('default', ['compile']);