describe('Resource Tests', function () {

  function waitForElement(locator) {
    browser.driver.wait(function () {
      return locator.isPresent()
    }, 10000);
  }

  beforeEach(function () {
    browser.get('https://repekadev.fslab.agh.edu.pl/admin/resources/books?currentPageNumber=1&resourcesPerPage=1000');
    browser.driver.manage().window().setSize(1536, 864);
  });

  it('Try to add without required metadata value', function () {
    waitForElement(element(by.cssContainingText('span', 'Dodaj')));
    var addResourceButton = element(by.cssContainingText('span', 'Dodaj'));
    addResourceButton.click();

    waitForElement(element(by.className('select2-selection__rendered')));
    var lists = element.all(by.className('select2-selection__rendered'));
    var listToClick = lists.get(1);
    browser.sleep(1000);
    listToClick.click();
    browser.sleep(500);
    browser.driver.switchTo().activeElement().sendKeys('Rodzaj_do_testowania_automatycznego');
    browser.driver.switchTo().activeElement().sendKeys(protractor.Key.ENTER);
    browser.sleep(500);

    waitForElement(element(by.buttonText('Dodaj')));
    var addRButton = element(by.buttonText('Dodaj'));
    addRButton.click();

    waitForElement(element(by.className('help-block validation-message')));
    var errorMessage = element(by.className('help-block validation-message'));
    expect(errorMessage.getText()).toContain('To pole jest wymagane');
  });

  it('Add resource', function () {
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
    var addButton = element(by.buttonText('Dodaj'));
    addButton.click();

    waitForElement(element(by.cssContainingText('span', 'Rodzaj_do_testowania_automatycznego')));
  });

  it('Try to remove required metadata value', function () {
    waitForElement(element(by.className('au-animate fade-inup-outdown au-target')));
    var resourcesList = element.all(by.className('au-animate fade-inup-outdown au-target'));
    var lastResource = resourcesList.get(0);
    lastResource.click();

    waitForElement(element(by.buttonText('Edytuj')));
    var editButton = element(by.buttonText('Edytuj'));
    editButton.click();

    browser.driver.manage().window().setSize(1280, 1024);
    waitForElement(element(by.className('buttons')));
    browser.sleep(1000);
    var buttonsPanels = element.all(by.className('buttons'));
    var firstValueButtonPanel = buttonsPanels.get(1);
    var deleteValueButton = firstValueButtonPanel.all(by.className('au-target')).get(0);
    browser.sleep(500);
    browser.actions().mouseMove(deleteValueButton).click().perform();

    waitForElement(element(by.buttonText('Zapisz')));
    var editButton = element(by.buttonText('Zapisz'));
    editButton.click();

    waitForElement(element(by.className('help-block validation-message')));
    var errorMessage = element(by.className('help-block validation-message'));
    expect(errorMessage.getText()).toContain('Wartość dla tej metadanej jest wymagana');
  });

  it('Modify metadata value', function () {
    alertDialog = browser.switchTo().alert();
    alertDialog.accept();

    waitForElement(element(by.className('au-animate fade-inup-outdown au-target')));
    var resourcesList = element.all(by.className('au-animate fade-inup-outdown au-target'));
    var lastResource = resourcesList.get(0);
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
    browser.sleep(1000);
    var metadataValuesList = element(by.className('resource-details'));
    expect(metadataValuesList.getText()).toContain('NewValue');
  });

  it('Remove optional metadata value', function () {
    waitForElement(element(by.className('au-animate fade-inup-outdown au-target')));
    var resourcesList = element.all(by.className('au-animate fade-inup-outdown au-target'));
    var lastResource = resourcesList.get(0);
    lastResource.click();

    waitForElement(element(by.buttonText('Edytuj')));
    var editButton = element(by.buttonText('Edytuj'));
    editButton.click();

    waitForElement(element(by.className('buttons')));
    browser.sleep(1000);
    var buttonsPanels = element.all(by.className('buttons'));
    var firstValueButtonPanel = buttonsPanels.get(2);
    var deleteValueButton = firstValueButtonPanel.all(by.className('au-target')).get(0);
    deleteValueButton.click();

    waitForElement(element(by.buttonText('Zapisz')));
    var editButton = element(by.buttonText('Zapisz'));
    editButton.click();

    waitForElement(element(by.className('resource-details')));
    browser.sleep(1000);
    var metadataValuesList = element(by.className('resource-details'));
    expect(metadataValuesList.getText()).not.toContain('Value2');
  });

  it('Delete resource', function () {
    waitForElement(element(by.className('au-animate fade-inup-outdown au-target')));
    var resourcesList = element.all(by.className('au-animate fade-inup-outdown au-target'));
    var lastResource = resourcesList.get(0);
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
