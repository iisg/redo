describe('Transition Tests', function() {

  function waitForElement (locator) {
	   browser.driver.wait(function () {
			return locator.isPresent()
	   }, 10000);
  }
 
  beforeEach(function() {
	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	browser.driver.manage().window().setSize(1536, 864);

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
	var addRButton = element(by.buttonText('Dodaj'));
	addRButton.click();

	waitForElement(element(by.cssContainingText('span', 'Rodzaj_do_testowania_automatycznego')));
  });

  // it('Try to make a transition without a proper designated person', function() {
	// browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	// waitForElement(element(by.className('current-page-number')));
	// browser.sleep(1000);
	// var resources = element.all(by.cssContainingText('td', 'Rodzaj_do_testowania_automatycznego'));
	// var lastResource = resources.get(0);
	// lastResource.click();

	// waitForElement(element(by.buttonText('Edytuj')));
	// var editButton = element(by.buttonText('Edytuj'));
	// editButton.click();

	// waitForElement(element(by.className('transparent au-target')));
	// browser.sleep(3000);
	// var newMetadataValuesFields = element.all(by.className('transparent au-target'));
	// var personMetadataValueField = newMetadataValuesFields.get(3);
	// var personMetadataValueButton = personMetadataValueField.all(by.className('au-target'));
	// personMetadataValueButton.get(2).click();
	// browser.sleep(500);

	// waitForElement(element(by.className('select2-selection__rendered')));
	// var lists = element.all(by.className('select2-selection__rendered'));
	// var listToClick = lists.get(1);
	// browser.sleep(1000);
	// listToClick.click();
	// browser.sleep(500);
	// browser.driver.switchTo().activeElement().sendKeys('#1000001');
	// browser.sleep(3000);
	// browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);
	// browser.sleep(500);

	// waitForElement(element(by.buttonText('Zapisz')));
	// var editButton = element(by.buttonText('Zapisz'));
	// editButton.click();

	// waitForElement(element(by.className('toggle-button disabled')));
	// browser.sleep(3000);

	// browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	// waitForElement(element(by.className('current-page-number')));
	// browser.sleep(1000);
	// var resources = element.all(by.cssContainingText('td', 'Rodzaj_do_testowania_automatycznego'));
	// var lastResource = resources.get(0);
	// lastResource.click();

	// waitForElement(element(by.cssContainingText('span', 'Usuń')));
	// var deleteButton = element(by.cssContainingText('span', 'Usuń'));
	// deleteButton.click();

	// waitForElement(element(by.className('swal2-confirm toggle-button red')));
	// var confirmButton = element.all(by.className('swal2-confirm toggle-button red'));
	// confirmButton.click();
	// browser.sleep(500);

	// waitForElement(element(by.className('current-page-number')));
	// browser.sleep(1000);
  // });

  it('Try to make a transition without required metadata', function() {
	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	waitForElement(element(by.className('current-page-number')));
	browser.sleep(1000);
	var resources = element.all(by.cssContainingText('td', 'Rodzaj_do_testowania_automatycznego'));
	var lastResource = resources.get(0);
	lastResource.click();

	waitForElement(element(by.buttonText('Edytuj')));
	var editButton = element(by.buttonText('Edytuj'));
	editButton.click();

	waitForElement(element(by.className('form-control au-target')));
	var metadataValuesFields = element.all(by.className('form-control au-target'));
	metadataValuesFields.get(1).clear();
	browser.sleep(500);

	waitForElement(element(by.className('select2-selection__rendered')));
	var lists = element.all(by.className('select2-selection__rendered'));
	var listToClick = lists.get(1);
	browser.sleep(1000);
	listToClick.click();
	browser.sleep(500);
	browser.driver.switchTo().activeElement().sendKeys('#1000000');
	browser.sleep(3000);
	browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);
	browser.sleep(500);

	waitForElement(element(by.buttonText('Zapisz')));
	var editButton = element(by.buttonText('Zapisz'));
	editButton.click();

	waitForElement(element(by.cssContainingText('span', 'przejście')));
	var transitionButton = element(by.cssContainingText('span', 'przejście'));
	transitionButton.click();

	waitForElement(element(by.className('buttons')));
	browser.sleep(1000);
	waitForElement(element(by.className('form-control au-target')));
	var dataFields = element.all(by.className('form-control au-target'));
	dataFields.get(0).clear();

	waitForElement(element(by.cssContainingText('span', 'przejdź')));
	var confirmTransitionButton = element(by.cssContainingText('span', 'przejdź'));
	confirmTransitionButton.click();

	waitForElement(element(by.className('help-block validation-message')));
	var errorMessage = element(by.className('help-block validation-message'));
    expect(errorMessage.getText()).toContain('To pole jest wymagane');

	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	alertDialog = browser.switchTo().alert();
	alertDialog.accept();
	waitForElement(element(by.className('current-page-number')));
	browser.sleep(1000);
	var resources = element.all(by.cssContainingText('td', 'Rodzaj_do_testowania_automatycznego'));
	var lastResource = resources.get(0);
	lastResource.click();

	waitForElement(element(by.cssContainingText('span', 'Usuń')));
	var deleteButton = element(by.cssContainingText('span', 'Usuń'));
	deleteButton.click();

	waitForElement(element(by.className('swal2-confirm toggle-button red')));
	var confirmButton = element.all(by.className('swal2-confirm toggle-button red'));
	confirmButton.click();
	browser.sleep(500);

	waitForElement(element(by.className('current-page-number')));
	browser.sleep(1000);
  });

   it('Automatic ascription of a designated person', function() {
	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	waitForElement(element(by.className('current-page-number')));
	browser.sleep(1000);
	var resources = element.all(by.cssContainingText('td', 'Rodzaj_do_testowania_automatycznego'));
	var lastResource = resources.get(0);
	lastResource.click();

	waitForElement(element(by.buttonText('Edytuj')));
	var editButton = element(by.buttonText('Edytuj'));
	editButton.click();

	waitForElement(element(by.className('select2-selection__rendered')));
	var lists = element.all(by.className('select2-selection__rendered'));
	var listToClick = lists.get(1);
	browser.sleep(1000);
	listToClick.click();
	browser.sleep(500);
	browser.driver.switchTo().activeElement().sendKeys('#1000000');
	browser.sleep(3000);
	browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);
	browser.sleep(500);

	waitForElement(element(by.buttonText('Zapisz')));
	var editButton = element(by.buttonText('Zapisz'));
	editButton.click();

	waitForElement(element(by.cssContainingText('span', 'przejście')));
	var transitionButton = element(by.cssContainingText('span', 'przejście'));
	transitionButton.click();

	waitForElement(element(by.cssContainingText('span', 'przejdź')));
	var confirmTransitionButton = element(by.cssContainingText('span', 'przejdź'));
	confirmTransitionButton.click();
	browser.sleep(1000);

	waitForElement(element(by.buttonText('Edytuj')));
    var metadataValuesList = element(by.className('resource-details'));
	expect(metadataValuesList.getText()).toContain('(admin)');

	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	waitForElement(element(by.className('current-page-number')));
	browser.sleep(1000);
	var resources = element.all(by.cssContainingText('td', 'Rodzaj_do_testowania_automatycznego'));
	var lastResource = resources.get(0);
	lastResource.click();

	waitForElement(element(by.cssContainingText('span', 'Usuń')));
	var deleteButton = element(by.cssContainingText('span', 'Usuń'));
	deleteButton.click();

	waitForElement(element(by.className('swal2-confirm toggle-button red')));
	var confirmButton = element.all(by.className('swal2-confirm toggle-button red'));
	confirmButton.click();
	browser.sleep(500);

	waitForElement(element(by.className('current-page-number')));
	browser.sleep(1000);
  });
  
  it('Sucessful transition', function() {
	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	waitForElement(element(by.className('current-page-number')));
	browser.sleep(1000);
	var resources = element.all(by.cssContainingText('td', 'Rodzaj_do_testowania_automatycznego'));
	var lastResource = resources.get(0);
	lastResource.click();

	waitForElement(element(by.buttonText('Edytuj')));
	var editButton = element(by.buttonText('Edytuj'));
	editButton.click();

	waitForElement(element(by.className('select2-selection__rendered')));
	var lists = element.all(by.className('select2-selection__rendered'));
	var listToClick = lists.get(1);
	browser.sleep(1000);
	listToClick.click();
	browser.sleep(500);
	browser.driver.switchTo().activeElement().sendKeys('#1000000');
	browser.sleep(3000);
	browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);
	browser.sleep(500);

	waitForElement(element(by.buttonText('Zapisz')));
	var editButton = element(by.buttonText('Zapisz'));
	editButton.click();

	waitForElement(element(by.cssContainingText('span', 'przejście')));
	var transitionButton = element(by.cssContainingText('span', 'przejście'));
	transitionButton.click();

	waitForElement(element(by.cssContainingText('span', 'przejdź')));
	var confirmTransitionButton = element(by.cssContainingText('span', 'przejdź'));
	confirmTransitionButton.click();
	browser.sleep(1000);

	waitForElement(element(by.buttonText('Edytuj')));
    var metadataValuesList = element(by.className('resource-details'));
	expect(metadataValuesList.getText()).toContain('KONIEC');

	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	waitForElement(element(by.className('current-page-number')));
	browser.sleep(1000);
	var resources = element.all(by.cssContainingText('td', 'Rodzaj_do_testowania_automatycznego'));
	var lastResource = resources.get(0);
	lastResource.click();

	waitForElement(element(by.cssContainingText('span', 'Usuń')));
	var deleteButton = element(by.cssContainingText('span', 'Usuń'));
	deleteButton.click();

	waitForElement(element(by.className('swal2-confirm toggle-button red')));
	var confirmButton = element.all(by.className('swal2-confirm toggle-button red'));
	confirmButton.click();
	browser.sleep(500);

	waitForElement(element(by.className('current-page-number')));
	browser.sleep(1000);
  });
});