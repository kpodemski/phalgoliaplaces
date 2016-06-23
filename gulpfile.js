var gulp        = require('gulp');
var del         = require('del');
var mkdirp      = require('mkdirp');
var glob        = require('glob-all');
var fs          = require('fs-extra');
var zip         = require('gulp-zip');
var runSequence = require('run-sequence');
var jscs        = require('gulp-jscs');
var sass        = require('gulp-sass');
var sourcemaps  = require('gulp-sourcemaps');
var notify      = require('gulp-notify');
var bourbon     = require('node-bourbon');
var gulpif      = require('gulp-if');
var rename      = require('gulp-rename');
/** @var {{ moduleName, themeModulePrefix, sourcemaps }} options **/
var options     = require('./package.json').options;

var copyIndexIgnore = [];

var moduleFolders = [
  './controllers',
  './models',
  './init',
  './translations',
  './views'
];

var cleanUp = [
  '/.sass-cache/',
  './views/css/**/*.css.map'
];

function displayNotification(msg) {
  return notify(msg);
}

gulp.task('compile-css', function() {
  return gulp.src('./views/sass/**/*.scss')
    .pipe(
      sass({
        includePaths: bourbon.includePaths,
        outputStyle: 'expanded',
        precision: 8
      }).on('error', sass.logError)
    )
    .pipe(gulpif(options.sourcemaps, sourcemaps.init()))
    .pipe(gulpif(options.sourcemaps, sourcemaps.write('./')))
    .pipe(gulp.dest('./views/css/'))
    .pipe(displayNotification({
      message: 'Module CSS compilation successful for ' + options.moduleName,
      onLast: true
    }));
});

gulp.task('sass:watch', function() {
  gulp.watch('./views/sass/**/*.scss', ['compile-css']);
});

gulp.task('clean-up', function() {
  return del(cleanUp).then(function() {
    console.log('Deleted files and folders:\n', cleanUp.join('\n'));
  });
});

gulp.task('copy-index', function(callback) {
  var total;
  var done  = 0;
  glob(['./**'], {ignore: copyIndexIgnore}, function(err, folders) {
    total = moduleFolders.length;
    if (total < 1 && callback) {
      callback();
    }

    moduleFolders.forEach(function(folder) {
      fs.copy('index.php.copy', folder + '/index.php', function(err) {
        if (err) {
          return console.error(err);
        }

        done++;
        if (done == total && callback) {
          callback();
        }
      });
    });
  });
});

gulp.task('format-js', function() {
  return gulp.src([
    './gulpfile.js',
    './views/js/**/*.js',
    '!./views/js/vendors/**/*.js'
  ],
  {base: __dirname})
  .pipe(jscs({fix: true}))
  .pipe(gulp.dest('./'));
});

gulp.task('create-zip', function() {
  fs.readFile('./config.xml', 'utf8', function(err, data) {
    if (err) {
      return console.error(err);
    }

    var moduleVersion = '';
    var matches = data.match(/<version><!\[CDATA\[(.*?)]]><\/version>/i);

    if (matches !== null && typeof matches[1] == 'string') {
      moduleVersion = matches[1].trim();
    }

    return gulp.src([
        '*',
        './controllers/**/*',
        './models/**/*',
        './init/**/*',
        './translations/**/*',
        './views/**/*',
        '!./composer.lock',
        '!./index.php.copy',
        '!./node_modules',
        '!./vendor',
        '!./*.zip',
      ],
      {base: './'}
    )
    .pipe(zip('v' + moduleVersion + '-' + options.moduleName + '.zip'))
    .pipe(gulp.dest('./'));
  });
});

gulp.task('build', function(callback) {
  runSequence(
    'compile-css',
    'clean-up',
    'format-js',
    'copy-index',
    'create-zip',
    callback
  );
});

gulp.task('default', ['build']);
