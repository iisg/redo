describe('Resource Type Tests', function() {

  function waitForElement (locator) {
	   browser.driver.wait(function () {
			return locator.isPresent()
	   }, 10000);
  }
  
  beforeEach(function() {
		browser.get('https://repekadev.fslab.agh.edu.pl/admin/resource-kinds/books');
		browser.driver.manage().window().maximize();
  });

  it('Try to add without metadata', function() {
		waitForElement(element(by.cssContainingText('span', 'Dodaj')));
		var addResourceTypeButton = element(by.cssContainingText('span', 'Dodaj'));
		browser.sleep(500);
		addResourceTypeButton.click();

		var fields = element.all(by.className('form-control au-target'));
		fields.get(0).sendKeys('Nazwa_Testowanie_Automatyczne');
		fields.get(1).sendKeys('Nazwa_Testowanie_Automatyczne');

		waitForElement(element(by.buttonText('Dodaj')));
		var addButton = element(by.buttonText('Dodaj'));
		addButton.click();

		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('Dodaj rodzaj metadanej.');
  });

  it('Try to add without Polish name', function() {
		var EC = protractor.ExpectedConditions;
	  
		waitForElement(element(by.cssContainingText('span', 'Dodaj')));
		var addResourceTypeButton = element(by.cssContainingText('span', 'Dodaj'));
		browser.sleep(500);
		addResourceTypeButton.click();

		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		fields.get(1).sendKeys('Nazwa_Testowanie_Automatyczne');
		
		waitForElement(element(by.className('select2-selection__rendered')));
		var lists = element.all(by.className('select2-selection__rendered'));
		var listToClick = lists.get(1);
		var isClickable = EC.elementToBeClickable(listToClick);
		browser.wait(isClickable, 10000);
		listToClick.click();
		browser.driver.switchTo().activeElement().sendKeys('Metadana_do_testowania_automatycznego1');
		browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);

		waitForElement(element(by.buttonText('Dodaj')));
		var addButton = element(by.buttonText('Dodaj'));
		addButton.click();
		
		waitForElement(element(by.className('help-block validation-message')));
		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('Nazwa wyświetlana musi mieć wartość we wszystkich językach.');
  });

  it('Try to add without English name', function() {
	  	var EC = protractor.ExpectedConditions;
		
		waitForElement(element(by.cssContainingText('span', 'Dodaj')));
		var addResourceTypeButton = element(by.cssContainingText('span', 'Dodaj'));
		browser.sleep(500);
		addResourceTypeButton.click();
		
		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		fields.get(0).sendKeys('Nazwa_Testowanie_Automatyczne');
		
		waitForElement(element(by.className('select2-selection__rendered')));
		var lists = element.all(by.className('select2-selection__rendered'));
		var listToClick = lists.get(1);
		var isClickable = EC.elementToBeClickable(listToClick);
		browser.wait(isClickable, 10000);
		listToClick.click();
		browser.driver.switchTo().activeElement().sendKeys('Metadana_do_testowania_automatycznego1');
		browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);

		waitForElement(element(by.buttonText('Dodaj')));
		var addButton = element(by.buttonText('Dodaj'));
		addButton.click();
		
		waitForElement(element(by.className('help-block validation-message')));
		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('Nazwa wyświetlana musi mieć wartość we wszystkich językach.');
  });

  it('Add resource type', function() {
	  	var EC = protractor.ExpectedConditions;
		
		waitForElement(element(by.cssContainingText('span', 'Dodaj')));
		var addResourceTypeButton = element(by.cssContainingText('span', 'Dodaj'));
		browser.sleep(500);
		addResourceTypeButton.click();

		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		fields.get(0).sendKeys('Nazwa_Testowanie_Automatyczne');
		fields.get(1).sendKeys('Nazwa_Testowanie_Automatyczne');

		waitForElement(element(by.className('select2-selection__rendered')));
		var lists = element.all(by.className('select2-selection__rendered'));
		var listToClick = lists.get(1);
		var isClickable = EC.elementToBeClickable(listToClick);
		browser.wait(isClickable, 10000);
		listToClick.click();
		browser.driver.switchTo().activeElement().sendKeys('Metadana_do_testowania_automatycznego1');
		browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);

		waitForElement(element(by.buttonText('Dodaj')));
		var addButton = element(by.buttonText('Dodaj'));
		addButton.click();
		browser.sleep(1000);

		waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		expect(element(by.linkText('Nazwa_Testowanie_Automatyczne')).isPresent()).toBe(true);
  });

  it('Try to edit by removing Polish name', function() {
	    waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newResourceTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newResourceTypeRowLink.click();

		waitForElement(element(by.buttonText('Edytuj')));
		var editButton = element(by.buttonText('Edytuj'));
		editButton.click();

		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		var confirmButton = element(by.buttonText('Zatwierdź'));
		fields.get(0).clear();
		confirmButton.click();

		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('Nazwa wyświetlana musi mieć wartość we wszystkich językach.');
  });

  it('Try to edit by removing English name', function() {
	    waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newResourceTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newResourceTypeRowLink.click();

		waitForElement(element(by.buttonText('Edytuj')));
		var editButton = element(by.buttonText('Edytuj'));
		editButton.click();

		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		var confirmButton = element(by.buttonText('Zatwierdź'));
		fields.get(1).clear();
		confirmButton.click();

		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('Nazwa wyświetlana musi mieć wartość we wszystkich językach.');
  });

  it('Try to edit by removing the only metadata', function() {
	    waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newResourceTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newResourceTypeRowLink.click();

		waitForElement(element(by.buttonText('Edytuj')));
		var editButton = element(by.buttonText('Edytuj'));
		editButton.click();

		waitForElement(element(by.className('buttons')));
		var metadataButtonsLabel = element(by.className('buttons'));
		var metadataButtons = metadataButtonsLabel.all(by.className('au-target'));
		metadataButtons.get(10).click();

		var confirmButton = element(by.buttonText('Zatwierdź'));
		confirmButton.click();

		var errorMessage = element(by.className('help-block validation-message'));
		expect(errorMessage.getText()).toContain('Dodaj rodzaj metadanej.');
  });

  it('Edit by adding metadata', function() {
	  	var EC = protractor.ExpectedConditions;
	  
	    waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newResourceTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newResourceTypeRowLink.click();

		waitForElement(element(by.buttonText('Edytuj')));
		var editButton = element(by.buttonText('Edytuj'));
		editButton.click();
		
		browser.sleep(1000);
		waitForElement(element(by.className('select2-selection__rendered')));
		var lists = element.all(by.className('select2-selection__rendered'));
		var listToClick = lists.get(1);
		var isClickable = EC.elementToBeClickable(listToClick);
		browser.wait(isClickable, 10000);
		listToClick.click();
		browser.driver.switchTo().activeElement().sendKeys('Metadana_do_testowania_automatycznego2');
		browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);		
		
		var confirmButton = element(by.buttonText('Zatwierdź'));
		confirmButton.click();
		browser.sleep(500);

		waitForElement(element(by.className('dl-horizontal')));
		var parametersList = element(by.className('dl-horizontal'));
		expect(parametersList.getText()).toContain('Metadana_do_testowania_automatycznego');
  });

  it('Edit by changing Polish name', function() {
	    waitForElement(element(by.linkText('Nazwa_Testowanie_Automatyczne')));
		var newResourceTypeRowLink = element(by.linkText('Nazwa_Testowanie_Automatyczne'));
		newResourceTypeRowLink.click();

		waitForElement(element(by.buttonText('Edytuj')));
		var editButton = element(by.buttonText('Edytuj'));
		editButton.click();

		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		var confirmButton = element(by.buttonText('Zatwierdź'));
		fields.get(0).clear();
		fields.get(0).sendKeys('Nowa_Nazwa_Automatyczna');
		confirmButton.click();
		browser.sleep(500);

		waitForElement(element(by.className('page-title')));
		var titleLabel = element(by.className('page-title'));
		expect(titleLabel.getText()).toContain('Nowa_Nazwa_Automatyczna');
  });

  it('Edit by changing English name', function() {
	    waitForElement(element(by.linkText('Nowa_Nazwa_Automatyczna')));
		var newResourceTypeRowLink = element(by.linkText('Nowa_Nazwa_Automatyczna'));
		newResourceTypeRowLink.click();

		waitForElement(element(by.buttonText('Edytuj')));
		var editButton = element(by.buttonText('Edytuj'));
		editButton.click();

		waitForElement(element(by.className('form-control au-target')));
		var fields = element.all(by.className('form-control au-target'));
		var confirmButton = element(by.buttonText('Zatwierdź'));
		fields.get(1).clear();
		fields.get(1).sendKeys('Nowa_Nazwa_AutomatycznaENG');
		confirmButton.click();
		browser.sleep(500);

		waitForElement(element(by.buttonText('Edytuj')));
		var languageMenu = element(by.className('au-target flag-icon-xs'));
		languageMenu.click();
		var englishOption = element(by.linkText('English'));
		englishOption.click();

		waitForElement(element(by.className('page-title')));
		var titleLabel = element(by.className('page-title'));
		expect(titleLabel.getText()).toContain('Nowa_Nazwa_AutomatycznaENG');

		var languageMenu = element(by.className('au-target flag-icon-xs'));
		languageMenu.click();
		var polishOption = element(by.linkText('Polski'));
		polishOption.click();
  });

  it('Delete resource type', function() {
		waitForElement(element(by.linkText('Nowa_Nazwa_Automatyczna')));
		var newResourceTypeRowLink = element(by.linkText('Nowa_Nazwa_Automatyczna'));
		newResourceTypeRowLink.click();

		waitForElement(element(by.buttonText('Usuń')));
		var deleteButton = element(by.buttonText('Usuń'));
		deleteButton.click();

		var confirmButton = element.all(by.className('swal2-confirm btn btn-danger'));
		confirmButton.click();

		waitForElement(element(by.cssContainingText('span', 'Dodaj')));
		expect(element(by.linkText('Nowa_Nazwa_Automatyczna')).isPresent()).toBe(false);
  });
});
