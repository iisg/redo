import {CustomValidationRule} from "../custom-validation-rules";
import {autoinject} from "aurelia-dependency-injection";
import {LanguageRepository, LanguagesChangedEvent} from "resources-config/language-config/language-repository";
import {EventAggregator} from "aurelia-event-aggregator";

@autoinject
export class RequiredInAllLanguagesValidationRule implements CustomValidationRule {
  static readonly NAME: string = 'RequiredInAllLanguages';

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
      let extraLanguages = $(languagesWithValue).not(this.knownLanguageCodes).get();
      return missingLanguages.length == 0 && extraLanguages.length == 0;
    };
  }
}
