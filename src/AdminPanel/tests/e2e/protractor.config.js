exports.config = {
  framework: 'jasmine',
  // seleniumAddress: 'http://localhost:4444/wd/hub',
  specs: ['LoginTests.js', 'MetadataTypeTests.js', 'ResourceTypeTests.js', 'ResourceTests.js'],
  getPageTimeout: 120000,
  allScriptsTimeout: 120000,
  multiCapabilities: [
    {
      'browserName': 'chrome'
    }
  ],

  onPrepare: function () {
    browser.ignoreSynchronization = true;
    jasmine.DEFAULT_TIMEOUT_INTERVAL = 180000;
  },
};
