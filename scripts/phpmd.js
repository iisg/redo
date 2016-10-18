/*
 It was impossible to create one command to lint all modules excluding frontend with PHPMD. There is an --exclude flag, but it does not
 prevent PHPMD from browser the whole FrontendModule directory (node_modules, jspm_packages etc) which was taking ages. This script
 excludes FrontendModule and calls PHPMD for every other module.

 If you want to run PHPMD for particular module "by hand", try:
 vendor/bin/phpmd src/MyModule text scripts/phpmd-rules.xml
 */
var fs = require('fs');
var path = require('path');
var exec = require('child_process').execSync;

function getDirectories(srcpath) {
  return fs.readdirSync(srcpath).filter(function (file) {
    return fs.statSync(path.join(srcpath, file)).isDirectory();
  });
}

function phpmdCommand(module) {
  return 'sh -c "vendor/bin/phpmd src/' + module + ' xml scripts/phpmd-rules.xml --reportfile var/reports/phpmd/' + module + '.xml"'
}

var modules = getDirectories('src').filter((dir) => dir != 'FrontendModule');
var lintFailed = false;
modules.forEach((module) => {
  try {
    exec(phpmdCommand(module));
  } catch (e) {
    lintFailed = true;
  }
});

if (lintFailed) {
  throw 'There are PHPMD violations (see var/reports/phpmd for details).';
}


