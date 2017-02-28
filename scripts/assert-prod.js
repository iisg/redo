'use strict';
const chalk = require('chalk');


if (process.env.REPEKA_ENV != 'prod') {
  console.log(chalk.bgRed('                                           '));
  console.log(chalk.bgRed('  This command must be run in `prod` env!  '));
  console.log(chalk.bgRed('                                           '));
  process.exit(1);
}
