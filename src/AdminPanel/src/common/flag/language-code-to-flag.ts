import {Language} from "../../resources-config/language-config/language";
import {autoinject} from "aurelia-dependency-injection";
import {LanguageRepository, LanguagesChangedEvent} from "../../resources-config/language-config/language-repository";
import {EventAggregator} from "aurelia-event-aggregator";

@autoinject
export class LanguageCodeToFlagValueConverter implements ToViewValueConverter {
  private languages: Language[];

  constructor(private languageRepository: LanguageRepository, eventAggregator: EventAggregator) {
    this.fetchLanguages();
    eventAggregator.subscribe(LanguagesChangedEvent, () => this.fetchLanguages());
  }

  private fetchLanguages() {
    this.languageRepository.getList().then(languages => {
      this.languages = languages;
    });
  }

  toView(languageCode: string): string {
    if (!this.languages || !languageCode) {
      return undefined;
    }
    let filteredLanguages = this.languages.filter(x => x.code.toLowerCase() == languageCode.toLowerCase());
    return filteredLanguages[0] ? filteredLanguages[0].flag : undefined;
  }
}
