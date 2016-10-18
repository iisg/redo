/*
 Creates list of all modules in the src/ directory except the FrontendModule.
 */
var fs = require('fs');
var path = require('path');

function getDirectories(srcpath) {
  return fs.readdirSync(srcpath).filter(function (file) {
    return fs.statSync(path.join(srcpath, file)).isDirectory();
  });
}

module.exports = getDirectories('src').filter((dir) => dir != 'FrontendModule');
