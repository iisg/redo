describe('Resource Tests', function() {

  function waitForElement (locator) {
	   browser.driver.wait(function () {
			return locator.isPresent()
	   }, 10000);
  } 
 
  beforeEach(function() {
		browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
		browser.driver.manage().window().setSize(1536, 864);
  });

  it('Try to add without required metadata value', function() {
	browser.switchTo().alert().then(
		function (alert) { alert.accept(); },
		function (err) { }
	);
	waitForElement(element(by.cssContainingText('span', 'Dodaj')));
	var addResourceButton = element(by.cssContainingText('span', 'Dodaj'));
	addResourceButton.click();

	waitForElement(element(by.className('current-page-number')));
	waitForElement(element(by.className('select2-selection__rendered')));
	var lists = element.all(by.className('select2-selection__rendered'));
	var listToClick = lists.get(1);
	browser.sleep(1000);
	listToClick.click();
	browser.sleep(500);
	browser.driver.switchTo().activeElement().sendKeys('Rodzaj_do_testowania_automatycznego');
	browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);
    browser.sleep(2000);

	waitForElement(element(by.buttonText('Dodaj')));
	var addRButton = element(by.buttonText('Dodaj'));
	addRButton.click();

	waitForElement(element(by.className('help-block validation-message')));
	var errorMessage = element(by.className('help-block validation-message'));
    expect(errorMessage.getText()).toContain('To pole jest wymagane');
  });

  it('Add resource', function() {
	waitForElement(element(by.cssContainingText('span', 'Dodaj')));
	var addResourceButton = element(by.cssContainingText('span', 'Dodaj'));
	addResourceButton.click();

	waitForElement(element(by.className('current-page-number')));
	waitForElement(element(by.className('select2-selection__rendered')));
	var lists = element.all(by.className('select2-selection__rendered'));
	var listToClick = lists.get(1);
	browser.sleep(1000);
	listToClick.click();
	browser.sleep(500);
	browser.driver.switchTo().activeElement().sendKeys('Rodzaj_do_testowania_automatycznego');
	browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);
	browser.sleep(500);

	waitForElement(element(by.className('form-control au-target')));
	var newMetadataValueFieldBlanks = element.all(by.className('form-control au-target'));
	newMetadataValueFieldBlanks.get(0).sendKeys('Value1');
	newMetadataValueFieldBlanks.get(1).sendKeys('Value2');

	waitForElement(element(by.buttonText('Dodaj')));
	var addButton = element(by.buttonText('Dodaj'));
	addButton.click();

	waitForElement(element(by.cssContainingText('span', 'Rodzaj_do_testowania_automatycznego')));
  });

  it('Try to remove required metadata value', function() {
	waitForElement(element(by.className('current-page-number')));
	var resources = element.all(by.cssContainingText('td', 'Rodzaj_do_testowania_automatycznego'));
	var lastResource = resources.get(0);
	lastResource.click();

	waitForElement(element(by.buttonText('Edytuj')));
	var editButton = element(by.buttonText('Edytuj'));
	editButton.click();

	browser.driver.manage().window().setSize(1280, 1024);
	waitForElement(element(by.className('form-control au-target')));
	var dataFields = element.all(by.className('form-control au-target'));
	dataFields.get(0).clear();

	waitForElement(element(by.buttonText('Zapisz')));
	var editButton = element(by.buttonText('Zapisz'));
	editButton.click();

	waitForElement(element(by.className('help-block validation-message')));
	var errorMessage = element(by.className('help-block validation-message'));
    expect(errorMessage.getText()).toContain('To pole jest wymagane');
  });

  it('Modify metadata value', function() {
	browser.switchTo().alert().then(
		function (alert) { alert.accept(); },
		function (err) { }
	);
		
	waitForElement(element(by.className('current-page-number')));
	var resources = element.all(by.cssContainingText('td', 'Rodzaj_do_testowania_automatycznego'));
	var lastResource = resources.get(0);
	lastResource.click();

	waitForElement(element(by.buttonText('Edytuj')));
	var editButton = element(by.buttonText('Edytuj'));
	editButton.click();

	waitForElement(element(by.className('form-control au-target')));
	browser.sleep(1000);
	var metadataValueFields = element.all(by.className('form-control au-target'));
	metadataValueFields.get(0).clear();
	metadataValueFields.get(0).sendKeys('NewValue');

	waitForElement(element(by.buttonText('Zapisz')));
	var editButton = element(by.buttonText('Zapisz'));
	editButton.click();

	waitForElement(element(by.className('resource-details')));
	browser.sleep(3000);
    var metadataValuesList = element(by.className('resource-details'));
	expect(metadataValuesList.getText()).toContain('NewValue');
  });

  it('Remove optional metadata value', function() {
	waitForElement(element(by.className('current-page-number')));
	var resources = element.all(by.cssContainingText('td', 'Rodzaj_do_testowania_automatycznego'));
	var lastResource = resources.get(0);
	lastResource.click();

	waitForElement(element(by.buttonText('Edytuj')));
	var editButton = element(by.buttonText('Edytuj'));
	editButton.click();

	waitForElement(element(by.className('form-control au-target')));
	browser.sleep(1000);
	var metadataValueFields = element.all(by.className('form-control au-target'));
	metadataValueFields.get(1).clear();

	waitForElement(element(by.buttonText('Zapisz')));
	var editButton = element(by.buttonText('Zapisz'));
	editButton.click();

	waitForElement(element(by.className('resource-details')));
	browser.sleep(1000);
    var metadataValuesList = element(by.className('resource-details'));
	expect(metadataValuesList.getText()).not.toContain('Value2');
  });

  it('Delete resource', function() {
	waitForElement(element(by.className('current-page-number')));
	var resources = element.all(by.cssContainingText('td', 'Rodzaj_do_testowania_automatycznego'));
	var lastResource = resources.get(0);
	lastResource.click();

	waitForElement(element(by.cssContainingText('span', 'Usuń')));
	var deleteButton = element(by.cssContainingText('span', 'Usuń'));
	deleteButton.click();

	waitForElement(element(by.className('swal2-confirm toggle-button red')));
	var confirmButton = element.all(by.className('swal2-confirm toggle-button red'));
	confirmButton.click();
	waitForElement(element(by.className('current-page-number')));

	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	expect(element(by.cssContainingText('td', 'Rodzaj_do_testowania_automatycznego')).isPresent()).toBe(false);
  });
});