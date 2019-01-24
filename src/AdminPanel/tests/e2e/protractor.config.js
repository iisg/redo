exports.config = {
  framework: 'jasmine2',
  specs: ['LoginTests.js', 'MetadataTypeTests.js', 'ResourceTypeTests.js', 'ResourceTests.js', 'TransitionTests.js'],
  //specs: ['LoginTests.js', 'MetadataTypeTests.js', 'ResourceTypeTests.js', 'ResourceTests.js', 'TransitionTests.js'],
  getPageTimeout: 120000,
  allScriptsTimeout: 120000,
  capabilities: {
    'browserName': 'chrome',
    chromeOptions: {
      args: ["--headless", '--no-sandbox', "--window-size=1536x864"]
    }
  },
  onPrepare: function () {
    browser.ignoreSynchronization = true;
    120000;
    120000;
    jasmine.DEFAULT_TIMEOUT_INTERVAL = 180000;
  },
};

//args: [ "--headless", "--disable-gpu", "--window-size=800x600", '--no-sandbox','-disable-popup-blocking', '-disable-dev-shm-usage' ]
