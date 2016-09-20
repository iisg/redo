var project = require("./repeka");
var chalk = require("chalk");

var printHelp = function (help) {
    project.printAsciiLogoAndVersion();

    console.log(chalk.grey("Usage: npm run ") + chalk.bgYellow(chalk.black("task")));
    console.log("");

    for (var task in help) {
        console.log(chalk.yellow(task) + "\t" + help[task]);
    }
}

module.exports = {
    printHelp: printHelp
};

var runningAsScript = require.main === module;
if (runningAsScript) {
    printHelp({
        "install": "Install project dependencies",
        "test": "Run tests"
    });
}
