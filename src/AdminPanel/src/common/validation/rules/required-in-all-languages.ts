import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {LanguageRepository, LanguagesChangedEvent} from "resources-config/language-config/language-repository";
import {CustomValidationRule} from "../custom-validation-rules";

@autoinject
export class RequiredInAllLanguagesValidationRule implements CustomValidationRule {
  static readonly NAME: string = 'requiredInAllLanguages';

  private knownLanguageCodes;

  constructor(private languageRepository: LanguageRepository, eventAggregator: EventAggregator) {
    this.fetchLanguages();
    eventAggregator.subscribe(LanguagesChangedEvent, () => this.fetchLanguages());
  }

  private fetchLanguages() {
    this.languageRepository.getList().then(languages => {
      this.knownLanguageCodes = languages.map(lang => lang.code);
    });
  }

  name(): string {
    return RequiredInAllLanguagesValidationRule.NAME;
  }

  validationFunction(): (object) => boolean {
    return (value) => {
      if (typeof value != 'object') {
        return true;
      }
      let languagesWithValue: any = [];
      for (let code in value) {
        if (value[code] && value[code].trim()) {
          languagesWithValue.push(code);
        }
      }
      let missingLanguages = $(this.knownLanguageCodes).not(languagesWithValue).get();
      return missingLanguages.length == 0;
    };
  }
}
