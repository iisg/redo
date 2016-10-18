/*
 Fix all fixable lint errors detected by CodeSniffer (PHP).
 https://github.com/squizlabs/PHP_CodeSniffer/wiki/Fixing-Errors-Automatically
 */
var modules = require('./backend-modules');
var exec = require('child_process').exec;

function phpcbfCommand(module) {
  return 'sh -c "vendor/bin/phpcbf src/' + module + ' --standard=scripts/phpcs-rules.xml'
}

modules.forEach((module) => exec(phpcbfCommand(module)).stdout.pipe(process.stdout));
