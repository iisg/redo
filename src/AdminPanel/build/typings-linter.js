'use strict';

const chalk = require('chalk');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

function loadTypings(typingsPath) {
  const typingsJson = require(path.join(process.cwd(), typingsPath));

  return Object.assign(
    typingsJson.dependencies || {},
    typingsJson.devDependencies || {},
    typingsJson.globalDependencies || {},
    typingsJson.globalDevDependencies || {}
  );
}

function loadTypingsIgnored(typingsPath) {
  const typingsJson = require(path.join(process.cwd(), typingsPath));

  return Object.assign(
    typingsJson.linterIgnored || {}
  );
}

function stripPrefix(str, separator) {
  const separatorPos = str.indexOf(separator);
  if (separatorPos == -1) {
    return null;
  }
  return str.substring(separatorPos + 1);
}

// breaks complex map entries into simple ones
function preprocessJspmMap(map) {
  const result = {};
  for (const key in map) {
    if (key.indexOf('@') == -1) {
      // simple map entry, just copy it
      result[key] = map[key];
      continue;
    }
    let packageName = stripPrefix(key, ':');
    const versionPos = packageName.indexOf('@');
    packageName = packageName.substr(0, versionPos);
    result[packageName] = key;
    for (const childKey in map[key]) {
      result[childKey] = map[key][childKey];
    }
  }
  return result;
}

function loadJspmConfig(configPath) {
  // create SystemJS stub
  let jspmConfigJson = {};
  const context = new vm.createContext({
    System: {
      // Stub for jspm.config.js
      config: obj => {
        if (obj.map) {
          jspmConfigJson = Object.assign(jspmConfigJson, preprocessJspmMap(obj.map));
        }
      }
    }
  });

  // execute file with stub
  const jspmConfigContents = fs.readFileSync(path.join(process.cwd(), configPath));
  const script = new vm.Script(jspmConfigContents);
  script.runInContext(context);
  return jspmConfigJson;
}

function printMismatched(mismatched) {
  for (const mismatch of mismatched) {
    console.log('- ' + chalk.red(mismatch.name) + ':');
    console.log('  jspm version:    %s', mismatch.jspmVersion);
    console.log('  typings version: %s', mismatch.typingsVersion);
  }
}

function printMissing(missing) {
  for (const missingPackage of missing) {
    console.log('- excessive typings: ' + chalk.yellow(missingPackage.name));
  }
}

function printOkayMessage(packageCount) {
  console.log(chalk.green('%d typings checked, no problems found'), packageCount);
}

function printMissingMessage(packageCount, missing) {
  console.log(chalk.bgYellow('%d typings checked, %d excessive entries found'), packageCount, missing.length);
  printMissing(missing);
}

function printMismatchedMessage(packageCount, missing, mismatched) {
  console.log(
    chalk.bgRed('%d typings checked, %d mismatches and %d excessive entries found'),
    packageCount, mismatched.length, missing.length
  );
  printMismatched(mismatched);
  printMissing(missing);
}

function printUnreliableMessage(unreliable) {
  console.log('Check skipped for %d packages because they come from sources with unreliable versioning:', unreliable.length);
  console.log(chalk.gray(unreliable.join(' ')));
}

function printIgnoredMessage(ignoredCount) {
  console.log('%d problems ignored due to rules in typings.json, check that file for details', ignoredCount);
}

module.exports = function checkTypingsVersions(typingsPath, jspmConfigPath, callback) {
  const typings = loadTypings(typingsPath);
  const jspmConfig = loadJspmConfig(jspmConfigPath);

  const missing = [];
  const mismatched = [];
  const ignored = loadTypingsIgnored(typingsPath);
  const unreliable = [];

  function InvalidEntry(name, typingsVersion, jspmVersion) {
    this.name = name;
    this.typingsVersion = typingsVersion;
    this.jspmVersion = jspmVersion;
  }

  let packageCount = 0;
  let ignoredCount = 0;
  for (const packageName in typings) {
    packageCount++;

    const typingsId = typings[packageName];
    const typingsVersion = stripPrefix(typingsId, '#');

    if (typingsId.startsWith('registry:')) {
      unreliable.push(packageName);
      continue;
    }

    if (!(packageName in jspmConfig)) {
      if (!(packageName in ignored)) {
        missing.push(new InvalidEntry(packageName, typingsVersion));
      } else {
        ignoredCount++;
      }
      continue;
    }

    const jspmId = jspmConfig[packageName];
    const jspmVersion = stripPrefix(jspmId, '@');

    if (typingsVersion != jspmVersion) {
      if (!(packageName in ignored)) {
        mismatched.push(new InvalidEntry(packageName, typingsVersion, jspmVersion));
      } else {
        ignoredCount++;
      }
    }
  }

  if (missing.length == 0 && mismatched.length == 0) {
    printOkayMessage(packageCount);
  } else if (mismatched.length == 0) {
    printMissingMessage(packageCount, missing);
  } else {
    printMismatchedMessage(packageCount, missing, mismatched);
  }
  if (unreliable.length) {
    printUnreliableMessage(unreliable);
  }
  if (ignoredCount) {
    printIgnoredMessage(ignoredCount);
  }
  if (missing.length > 0 || mismatched.length > 0) {
    throw new Error('Typings problems found');
  }

  if (callback) {
    callback();
  }
};
