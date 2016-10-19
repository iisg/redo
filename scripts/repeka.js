var version = require('./version');
var chalk = require('chalk');

var ASCII_LOGO_WIDTH = 33;
var LOGO =
  '  ____      ____      _  __     \n' +
  ' |  _ \\ ___|  _ \\ ___| |/ /__ _ \n' +
  ' | |_) / _ \\ |_) / _ \\ \' // _` |\n' +
  ' |  _ <  __/  __/  __/ . \\ (_| |\n' +
  ' |_| \\_\\___|_|   \\___|_|\\_\\__,_|';

var printAsciiLogoAndVersion = function () {
  var versionWithV = 'v' + version.text;
  var versionLine = Array(ASCII_LOGO_WIDTH - versionWithV.length).join(' ') + versionWithV;
  console.log(chalk.cyan(LOGO));
  console.log(chalk.green(versionLine));
};

module.exports = {
  printAsciiLogoAndVersion: printAsciiLogoAndVersion
};

var runningAsScript = require.main === module;
if (runningAsScript) {
  printAsciiLogoAndVersion();
}
