describe('Transition Tests', function() {

  function waitForElement (locator) {
	   browser.driver.wait(function () {
			return locator.isPresent()
	   }, 10000);
  }

  beforeEach(function() {
	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	browser.driver.manage().window().maximize();

	waitForElement(element(by.cssContainingText('span', 'Dodaj')));
	var addResourceButton = element(by.cssContainingText('span', 'Dodaj'));
	addResourceButton.click();

	waitForElement(element(by.className('current-page-number')));
	waitForElement(element(by.className('select2-selection__rendered')));
	var lists = element.all(by.className('select2-selection__rendered'));
	var listToClick = lists.get(1);
	browser.sleep(500);
	listToClick.click();
	browser.sleep(500);
	browser.driver.switchTo().activeElement().sendKeys('Rodzaj_do_testowania_automatycznego');
	browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);
	browser.sleep(500);

	waitForElement(element(by.className('new-metadata-value-button-container')));
	waitForElement(element(by.className('form-control au-target')));
	var metadataValueFieldBlanks = element(by.className('form-control au-target'));
	metadataValueFieldBlanks.sendKeys('Value1');

	var newMetadataValuesFields = element.all(by.className('new-metadata-value-button-container'));
	var secondMetadataValueField = newMetadataValuesFields.get(0);
	var secondMetadataValueButton = secondMetadataValueField.all(by.className('au-target'));
	secondMetadataValueButton.get(0).click();

	waitForElement(element(by.className('form-control au-target')));
	var newMetadataValueFieldBlanks = element.all(by.className('form-control au-target'));
	newMetadataValueFieldBlanks.get(1).sendKeys('Value2');

	waitForElement(element(by.buttonText('Dodaj')));
	var addRButton = element(by.buttonText('Dodaj'));
	addRButton.click();

	waitForElement(element(by.className('au-target resources-list')));
  });

  it('Try to make a transition without any designated person', function() {
	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	waitForElement(element(by.className('au-animate fade-inup-outdown au-target')));
	var resourcesList = element.all(by.className('au-animate fade-inup-outdown au-target'));
	var lastResource = resourcesList.get(0);
	lastResource.click();

	waitForElement(element(by.cssContainingText('span', 'przejście')));
	var transitionButton = element(by.cssContainingText('span', 'przejście'));
	transitionButton.click();

	expect(element(by.buttonText('Edytuj')).isPresent()).toBe(true);

	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	waitForElement(element(by.className('au-animate fade-inup-outdown au-target')));
	var resourcesList = element.all(by.className('au-animate fade-inup-outdown au-target'));
	var lastResource = resourcesList.get(0);
	lastResource.click();

	waitForElement(element(by.cssContainingText('span', 'Usuń')));
	var deleteButton = element(by.cssContainingText('span', 'Usuń'));
	deleteButton.click();

	var confirmButton = element.all(by.className('swal2-confirm toggle-button red'));
	confirmButton.click();
	browser.sleep(500);

	waitForElement(element(by.className('au-target resources-list')));
	browser.sleep(1000);
  });

  it('Try to make a transition without a proper designated person', function() {
	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	waitForElement(element(by.className('au-animate fade-inup-outdown au-target')));
	var resourcesList = element.all(by.className('au-animate fade-inup-outdown au-target'));
	var lastResource = resourcesList.get(0);
	lastResource.click();

	waitForElement(element(by.buttonText('Edytuj')));
	var editButton = element(by.buttonText('Edytuj'));
	editButton.click();

	waitForElement(element(by.className('new-metadata-value-button-container')));
	var newMetadataValuesFields = element.all(by.className('new-metadata-value-button-container'));
	var personMetadataValueField = newMetadataValuesFields.get(0);
	var personMetadataValueButton = personMetadataValueField.all(by.className('au-target'));
	personMetadataValueButton.get(0).click();

	waitForElement(element(by.className('select2-selection__rendered')));
	var personLists = element.all(by.className('select2-selection__rendered'));
	browser.sleep(500);
	personLists.click();
	browser.sleep(1000);
	browser.driver.switchTo().activeElement().sendKeys('ZZZ_automatyczne_testowanie');
	browser.sleep(1500);
	browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);
	browser.sleep(500);

	waitForElement(element(by.buttonText('Zapisz')));
	var editButton = element(by.buttonText('Zapisz'));
	editButton.click();

	waitForElement(element(by.cssContainingText('span', 'przejście')));
	var transitionButton = element(by.cssContainingText('span', 'przejście'));
	transitionButton.click();

	browser.sleep(3000);
	expect(element(by.buttonText('Edytuj')).isPresent()).toBe(true);

	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	waitForElement(element(by.className('au-animate fade-inup-outdown au-target')));
	var resourcesList = element.all(by.className('au-animate fade-inup-outdown au-target'));
	var lastResource = resourcesList.get(0);
	lastResource.click();

	waitForElement(element(by.cssContainingText('span', 'Usuń')));
	var deleteButton = element(by.cssContainingText('span', 'Usuń'));
	deleteButton.click();

	var confirmButton = element.all(by.className('swal2-confirm toggle-button red'));
	confirmButton.click();
	browser.sleep(500);

	waitForElement(element(by.className('au-target resources-list')));
	browser.sleep(1000);
  });

  it('Try to make a transition without required metadata', function() {
	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	waitForElement(element(by.className('au-animate fade-inup-outdown au-target')));
	var resourcesList = element.all(by.className('au-animate fade-inup-outdown au-target'));
	var lastResource = resourcesList.get(0);
	lastResource.click();

	waitForElement(element(by.buttonText('Edytuj')));
	var editButton = element(by.buttonText('Edytuj'));
	editButton.click();

	waitForElement(element(by.className('new-metadata-value-button-container')));
	var newMetadataValuesFields = element.all(by.className('new-metadata-value-button-container'));
	var personMetadataValueField = newMetadataValuesFields.get(0);
	var personMetadataValueButton = personMetadataValueField.all(by.className('au-target'));
	personMetadataValueButton.get(0).click();
	browser.sleep(500);

	waitForElement(element(by.className('select2-selection__rendered')));
	var personLists = element.all(by.className('select2-selection__rendered'));
	browser.sleep(500);
	personLists.click();
	browser.sleep(1000);
	browser.driver.switchTo().activeElement().sendKeys('AAA_testowanie_automatyczne');
	browser.sleep(1500);
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
	var buttonsPanels = element.all(by.className('buttons'));
	var firstValueButtonPanel = buttonsPanels.get(1);
	var deleteValueButton = firstValueButtonPanel.all(by.className('au-target')).get(0);
	deleteValueButton.click();

	waitForElement(element(by.cssContainingText('span', 'przejdź')));
	var confirmTransitionButton = element(by.cssContainingText('span', 'przejdź'));
	confirmTransitionButton.click();

	waitForElement(element(by.className('help-block validation-message')));
	var errorMessage = element(by.className('help-block validation-message'));
    expect(errorMessage.getText()).toContain('Wartość dla tej metadanej jest wymagana');

	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	alertDialog = browser.switchTo().alert();
	alertDialog.accept();
	waitForElement(element(by.className('au-animate fade-inup-outdown au-target')));
	var resourcesList = element.all(by.className('au-animate fade-inup-outdown au-target'));
	var lastResource = resourcesList.get(0);
	lastResource.click();

	waitForElement(element(by.cssContainingText('span', 'Usuń')));
	var deleteButton = element(by.cssContainingText('span', 'Usuń'));
	deleteButton.click();

	var confirmButton = element.all(by.className('swal2-confirm toggle-button red'));
	confirmButton.click();
	browser.sleep(500);

	waitForElement(element(by.className('au-target resources-list')));
	browser.sleep(1000);
  });

  it('Automatic ascription of a designated person', function() {
	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	waitForElement(element(by.className('au-animate fade-inup-outdown au-target')));
	var resourcesList = element.all(by.className('au-animate fade-inup-outdown au-target'));
	var lastResource = resourcesList.get(0);
	lastResource.click();

	waitForElement(element(by.buttonText('Edytuj')));
	var editButton = element(by.buttonText('Edytuj'));
	editButton.click();

	waitForElement(element(by.className('new-metadata-value-button-container')));
	var newMetadataValuesFields = element.all(by.className('new-metadata-value-button-container'));
	var personMetadataValueField = newMetadataValuesFields.get(0);
	var personMetadataValueButton = personMetadataValueField.all(by.className('au-target'));
	personMetadataValueButton.get(0).click();

	waitForElement(element(by.className('select2-selection__rendered')));
	var personLists = element.all(by.className('select2-selection__rendered'));
	browser.sleep(500);
	personLists.click();
	browser.sleep(1000);
	browser.driver.switchTo().activeElement().sendKeys('AAA_testowanie_automatyczne');
	browser.sleep(1500);
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
	expect(metadataValuesList.getText()).toContain('admin');

	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	waitForElement(element(by.className('au-animate fade-inup-outdown au-target')));
	var resourcesList = element.all(by.className('au-animate fade-inup-outdown au-target'));
	var lastResource = resourcesList.get(0);
	lastResource.click();

	waitForElement(element(by.cssContainingText('span', 'Usuń')));
	var deleteButton = element(by.cssContainingText('span', 'Usuń'));
	deleteButton.click();

	var confirmButton = element.all(by.className('swal2-confirm toggle-button red'));
	confirmButton.click();
	browser.sleep(500);

	waitForElement(element(by.className('au-target resources-list')));
	browser.sleep(1000);
  });

  it('Sucessful transition', function() {
	browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
	waitForElement(element(by.className('au-animate fade-inup-outdown au-target')));
	var resourcesList = element.all(by.className('au-animate fade-inup-outdown au-target'));
	var lastResource = resourcesList.get(0);
	lastResource.click();

	waitForElement(element(by.buttonText('Edytuj')));
	var editButton = element(by.buttonText('Edytuj'));
	editButton.click();

	waitForElement(element(by.className('new-metadata-value-button-container')));
	var newMetadataValuesFields = element.all(by.className('new-metadata-value-button-container'));
	var personMetadataValueField = newMetadataValuesFields.get(0);
	var personMetadataValueButton = personMetadataValueField.all(by.className('au-target'));
	personMetadataValueButton.get(0).click();

	waitForElement(element(by.className('select2-selection__rendered')));
	var personLists = element.all(by.className('select2-selection__rendered'));
	browser.sleep(500);
	personLists.click();
	browser.sleep(1000);
	browser.driver.switchTo().activeElement().sendKeys('AAA_testowanie_automatyczne');
	browser.sleep(1500);
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
	waitForElement(element(by.className('au-animate fade-inup-outdown au-target')));
	var resourcesList = element.all(by.className('au-animate fade-inup-outdown au-target'));
	var lastResource = resourcesList.get(0);
	lastResource.click();

	waitForElement(element(by.cssContainingText('span', 'Usuń')));
	var deleteButton = element(by.cssContainingText('span', 'Usuń'));
	deleteButton.click();

	var confirmButton = element.all(by.className('swal2-confirm toggle-button red'));
	confirmButton.click();
	browser.sleep(500);

	waitForElement(element(by.className('au-target resources-list')));
	browser.sleep(1000);
  });
});