'use strict';

const project = require("./repeka");
const version = require('./version');

const async = require('async');
const chalk = require('chalk');
const del = require('del');
const dos2unix = require('@dpwolfe/dos2unix').dos2unix;
const exec = require('child_process').exec;
const fs = require('fs-extra');
const ora = require('ora');
const preprocess = require('preprocess');

project.printAsciiLogoAndVersion();

const releaseFilename = process.env.RELEASE_FILENAME || 'repeka-' + version.text + '-' + version.full.hash + '.tar.gz';
const extensionsList = process.env.INCLUDE_EXTENSIONS || undefined;

console.log('');

function start() {
  clearVendorDirectory();
}

function clearVendorDirectory() {
  var spinner = ora({text: 'Cleaning vendor directory.', color: 'yellow'}).start();
  del('vendor/**/.git')
    .then(() => {
      spinner.succeed('Vendor directory cleaned.');
      clearReleaseDirectory();
    })
    .catch((err) => {
      console.log(err);
      spinner.fail();
    })
}

function clearReleaseDirectory() {
  var spinner = ora({text: 'Deleting release directory.', color: 'yellow'}).start();
  fs.remove('release/', function (err) {
    if (err) {
      spinner.fail();
      console.error(err);
    } else {
      spinner.succeed('Release directory deleted.');
      copyToReleaseDirectory();
    }
  });
}

function copyToReleaseDirectory() {
  var spinner = ora({text: 'Copying application files.', color: 'yellow'}).start();
  var calls = [];
  [
    'app/',
    'bin/',
    'docker/',
    'src/Repeka/Application',
    'src/Repeka/Domain',
    'src/Repeka/Plugins',
    'var/config/sample-configs',
    'vendor/',
    'web/',
  ].forEach(function (filename) {
    calls.push(function (callback) {
      fs.mkdirsSync('release/' + filename);
      fs.copy(filename, 'release/' + filename, function (err) {
        if (!err) {
          callback(err);
        } else {
          callback(null, filename);
        }
      });
    });
  });
  async.series(calls, function (err) {
    if (err) {
      spinner.fail();
      console.error(err);
    } else {
      createVarDirectoryStructure();
      copySingleRequiredFiles();
      removeUnwantedFiles();
      removeUnwantedExtensions();
      preprocessSources();
      spinner.succeed('Application files copied.');
      convertToUnixLineEndings();
    }
  });
}

function createVarDirectoryStructure() {
  [
    'var/backups',
    'var/cache',
    'var/import',
    'var/logs',
    'var/sessions',
    'var/ssl',
    'var/uploads',
    'var/volumes/elasticsearch',
    'var/volumes/metrics/data/whisper',
    'var/volumes/metrics/data/elasticsearch',
    'var/volumes/metrics/data/grafana',
    'var/volumes/metrics/log/graphite/webapp',
    'var/volumes/metrics/log/elasticsearch',
    'var/volumes/postgres'
  ].forEach(function (dirname) {
    fs.mkdirsSync('release/' + dirname);
  });
}

function copySingleRequiredFiles() {
  [
    'src/.htaccess',
    'var/.htaccess',
    'var/config/docker.env.sample',
    'var/config/user_data_mapping.json.sample',
    'var/config/grafana/custom.ini.sample',
    'var/config/proxy/trusted-proxies.sample.php',
    'var/volumes/initialize-directory-structure.sh',
  ].forEach((filepath) => {
    fs.copySync(filepath, 'release/' + filepath);
  });
}

function removeUnwantedFiles() {
  del.sync([
    'release/docker/.env',
    'release/docker/jenkins-agent',
    'release/app/config/config_dev.yml',
    'release/app/config/config_test.yml',
    'release/app/config/routing_dev.yml',
    'release/src/Repeka/Plugins/**/Tests',
    'release/**/.gitignore',
    'release/composer.*',
    'release/**/package.json',
    'release/web/admin/dist',
  ]);
}

function removeUnwantedExtensions() {
  if (extensionsList) {
    const extensionsNames = extensionsList.split(',').map(p => p.trim()).filter(p => p);
    const directoriesToRemove = [
      'release/app/Resources/views/**',
      '!release/app/Resources/views',
      '!release/app/Resources/views/*.twig',
      'release/src/Repeka/Plugins/**',
      '!release/src/Repeka/Plugins',
      'release/docker/docker-compose.*.yml',
      'release/docker/repeka/php.*.ini',
      '!release/docker/docker-compose.persistent.yml',
      '!release/docker/docker-compose.standalone.yml',
      '!release/docker/docker-compose.webproxy.yml',
      'release/var/config/sample-configs/*',
      '!release/var/config/sample-configs/config_local.dev.yml',
      'release/web/files/*',
      '!release/web/files/fonts',
      '!release/web/files/dummy-flag.svg',
      '!release/web/files/flags.json',
      '!release/web/files/icons.svg',
      'release/web/themes/*',
    ];
    for (let extensionName of extensionsNames) {
      directoriesToRemove.push(`!release/app/Resources/views/${extensionName.toLowerCase()}/**`);
      directoriesToRemove.push(`!release/docker/docker-compose.${extensionName.toLowerCase()}.yml`);
      const extensionNameCapitalizedFirst = extensionName.charAt(0).toUpperCase() + extensionName.slice(1);
      directoriesToRemove.push(`!release/src/Repeka/Plugins/${extensionNameCapitalizedFirst}/**`);
      directoriesToRemove.push(`!release/var/config/sample-configs/config_local.${extensionName.toLowerCase()}.yml`);
      directoriesToRemove.push(`!release/web/files/${extensionName.toLowerCase()}/**`);
      directoriesToRemove.push(`!release/web/themes/${extensionName.toLowerCase()}.*`);
    }
    for (let extensionName of extensionsNames) {
      const possibleExtensionPhpIni = `release/docker/repeka/php.${extensionName.toLowerCase()}.ini`;
      if (fs.existsSync(possibleExtensionPhpIni)) {
        const targetPhpIni = 'release/docker/repeka/php.ini';
        fs.unlinkSync(targetPhpIni);
        fs.renameSync(possibleExtensionPhpIni, targetPhpIni);
        break;
      }
    }
    del.sync(directoriesToRemove);
  }
}

function preprocessSources() {
  preprocess.preprocessFileSync('release/docker/repeka/Dockerfile', 'release/docker/repeka/Dockerfile', {}, {type: 'shell'});
}

function convertToUnixLineEndings() {
  var spinner = ora({text: 'Converting all line endings to unix.', color: 'yellow'}).start();
  new dos2unix()
    .on('error', (err) => {
      console.log(err);
      spinner.fail();
    })
    .on('end', () => {
      spinner.succeed('All line endings converted to unix.');
      deleteUnwantedSources();
    })
    .process(['release/app/**', 'release/docker/**', 'release/src/**', 'release/var/**']);
}

function deleteUnwantedSources() {
  var spinner = ora({text: 'Deleting unneeded sources.', color: 'yellow'}).start();
  del([
    'release/**/composer.json',
    'release/**/composer.lock',
    'release/vendor/**/*.md',
    'release/vendor/**/*.dist',
    'release/vendor/**/.idea/**',
    'release/vendor/**/doc/**',
    'release/vendor/**/docs/**',
    'release/vendor/**/img/**',
    'release/vendor/**/LICENSE',
  ]).then(() => {
    spinner.succeed('Unneeded sources deleted.');
    copyJsDependencies();
  })
    .catch((err) => {
      console.log(err);
      spinner.fail();
    })
}

function copyJsDependencies() {
  var spinner = ora({text: 'Copying JS dependencies.', color: 'yellow'}).start();
  del.sync('release/web/jspm_packages');
  fs.mkdirsSync('release/web/jspm_packages');
  fs.copy('src/AdminPanel/jspm_packages', 'release/web/jspm_packages', function (err) {
    if (err) {
      spinner.fail();
      console.error(err);
    } else {
      spinner.succeed('JS dependencies copied.');
      includeInstallationDocs();
    }
  });
}

function includeInstallationDocs() {
  var spinner = ora({text: 'Including installation docs.', color: 'yellow'}).start();
  exec('git fetch ' +
    '&& git show origin/docs:Installation.pdf > release/Installation.pdf ' +
    '&& git show origin/docs:Upgrading.pdf > release/Upgrading.pdf ' +
    '&& git show origin/docs:Changelog.pdf > release/Changelog.pdf', function (err) {
    if (err) {
      spinner.fail();
      console.error(err);
    } else {
      spinner.succeed('Installation docs included.');
      createZipArchive();
    }
  });
}

function createZipArchive() {
  var spinner = ora({text: 'Creating release archive.', color: 'yellow'}).start();
  exec('tar -czf ' + releaseFilename + ' release --transform=\'s/release\\/\\{0,1\\}//g\'', function (err) {
    if (err) {
      spinner.fail();
      console.log(err);
    } else {
      spinner.succeed('Release archive created.');
      console.log('');
      console.log("Package: " + chalk.green(releaseFilename));
      var fileSizeInBytes = fs.statSync(releaseFilename).size;
      console.log('Size: ' + Math.round(fileSizeInBytes / 1024) + 'kB (' + Math.round(fileSizeInBytes * 10 / 1024 / 1024) / 10 + 'MB)');
    }
  });
}

start();
