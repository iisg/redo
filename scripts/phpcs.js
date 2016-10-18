/*
 It was impossible to create one command to lint all modules excluding frontend with PHPCS. There is an --ignore flag, but it does not
 prevent PHPCS from browsing the whole FrontendModule directory (node_modules, jspm_packages etc) which was taking ages. This script
 excludes FrontendModule and calls PHPCS for every other module.

 If you want to run PHPCS for particular module "by hand", try:
 vendor/bin/phpcs src/MyModule --standard=scripts/phpcs-rules.xml

 If you want PHPCBF to automatically correct the violations, run:
 vendor/bin/phpcbf src/MyModule --standard=scripts/phpcs-rules.xml
 */
var modules = require('./backend-modules');
var exec = require('child_process').execSync;

function phpcsCommand(module) {
  return 'sh -c "vendor/bin/phpcs src/' + module + ' --standard=scripts/phpcs-rules.xml --report=checkstyle --report-file=var/reports/lints/phpcs-' + module + '.xml"'
}

var lintFailed = false;
modules.forEach((module) => {
  try {
    exec(phpcsCommand(module));
  } catch (e) {
    lintFailed = true;
  }
});

if (lintFailed) {
  throw 'There are PHPCS violations (see var/reports/lints for details).';
}
