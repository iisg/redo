var project = require("./repeka");
var chalk = require("chalk");

var printHelp = function (help) {
  project.printAsciiLogoAndVersion();

  console.log(chalk.grey("Usage: npm run ") + chalk.bgYellow(chalk.black("task")));
  console.log("");

  for (var task in help) {
    console.log(chalk.yellow(task) + "\t" + help[task]);
  }
};

module.exports = {
  printHelp: printHelp
};

var runningAsScript = require.main === module;
if (runningAsScript) {
  printHelp({
    "install": "Install project dependencies",
    "dist": "Prepare the app for production",
    "watch": "Watch for source code changes and build them continuously",
    "lint": "Analyse the source code for potential errors",
    "test": "Run tests",
    "check": "lint & test"
  });
}
