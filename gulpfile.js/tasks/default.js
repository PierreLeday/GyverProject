var config       = require('../config')
var gulp         = require('gulp')
var gulpSequence = require('gulp-sequence')

var defaultTask = function(cb) {
    gulpSequence('css', ['jsVendor', 'jsApp'], cb)
}

gulp.task('default', defaultTask)
module.exports = defaultTask
