var fs = require('fs');
var chalk = require('chalk');

var localConfigPath = "var/config/config_local.yml";

function check() {
  try {
    fs.accessSync(localConfigPath, fs.F_OK);
  } catch (e) {
    console.log(chalk.red('NO LOCAL CONFIG FILE'));
    console.log("Create local configuration in " + chalk.red(localConfigPath) + " and try again.");
    console.log("Run " + chalk.yellow("node scripts/local-config-checker.js") + " to create sample config file.");
    process.exit(1);
  }
}

module.exports = check;

var runningAsScript = require.main === module;
if (runningAsScript) {
  try {
    fs.accessSync(localConfigPath, fs.F_OK);
  } catch (e) {
    fs.createReadStream(localConfigPath + '.sample').pipe(fs.createWriteStream(localConfigPath));
  }
}
