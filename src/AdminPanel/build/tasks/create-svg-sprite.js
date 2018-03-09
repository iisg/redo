var gulp = require('gulp');
var path = require('path');
var paths = require('../paths');
var svgSprite = require('gulp-svg-sprite');

gulp.task('create-svg-sprite', () => {
  return gulp.src(path.join(paths.icons, '*.svg'))
    .pipe(svgSprite({
      shape: {
        transform: [
          {
            custom: function(shape, sprite, callback) {
              shape.setSVG(shape.getSVG().replace(/style=".*?"/g, ''));
              callback();
            }
          },
          'svgo',
        ]
      },
      mode: {
        defs: {
          dest: 'files/',
          sprite: 'icons.svg',
          example: {
            dest: path.join(paths.icons, '../icons.html')
          }
        }
      }
    }))
    .pipe(gulp.dest(paths.webRoot));
});
