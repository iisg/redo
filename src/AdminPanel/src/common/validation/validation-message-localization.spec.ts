import {installValidationMessageLocalization} from "./validation-message-localization";
import {Aurelia} from "aurelia-framework";
import {ValidationMessageProvider} from "aurelia-validation";
import {I18N} from "aurelia-i18n";

describe(installValidationMessageLocalization.name, () => {
  beforeEach(() => {
    this.translator = jasmine.createSpy('translator');
    const container = {
      get: (service) => {
        switch (service) {
          case I18N:
            return {'tr': this.translator};
        }
        throw new Error(`Unexpected service requested: ${service.name}`);
      }
    };
    const aureliaMock = {container};
    installValidationMessageLocalization(aureliaMock as Aurelia);
    this.validationMessageProvider = new ValidationMessageProvider({
      'parse': m => m
    });
  });

  it('translates arbitrary rule name', () => {
    this.translator.and.returnValue('testMessage');
    expect(this.validationMessageProvider.getMessage('testRule')).toEqual('testMessage');
  });

  it('strips rule name suffix', () => {
    this.translator.and.callFake(param => param);
    expect(this.validationMessageProvider.getMessage('testValidationRule')).toEqual('validation::test');
  });
});
