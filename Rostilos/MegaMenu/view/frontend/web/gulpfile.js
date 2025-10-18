var gulp = require('gulp');
var gulpless = require('gulp-less');
var gulpsourcemaps = require('gulp-sourcemaps');
var gulpautoprefixer = require('gulp-autoprefixer');

//Creating a Style task that convert LESS to CSS

gulp.task('styles', function () {
    var srcfile = './css/source/module.less';
    var dest = './css';
    return gulp
        .src(srcfile)
        .pipe(gulpsourcemaps.init())
        .pipe(gulpless())
        .pipe(gulpautoprefixer({browsers: ['last 2 versions', '>5%']}))
        .pipe(gulpsourcemaps.write(dest))
        .pipe(gulp.dest(dest));
});
