import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {bindingMode} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {Language} from "../../language-config/language";
import {LanguageRepository, LanguagesChangedEvent} from "../../language-config/language-repository";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";

@autoinject
export class LanguageChooser implements ComponentAttached, ComponentDetached {
  languages: Language[];
  @bindable({defaultBindingMode: bindingMode.twoWay})
  language: Language;

  private readonly changeSubscriber: Subscription;

  constructor(private languageRepository: LanguageRepository, private eventAggregator: EventAggregator) {
    this.changeSubscriber = eventAggregator.subscribe(LanguagesChangedEvent, () => this.fetchLanguages());
  }

  private fetchLanguages() {
    this.languageRepository.getList().then(languages => {
      this.languages = languages;
      this.setCurrentLanguage(this.languages[0]);
    });
  }

  attached(): void {
    this.fetchLanguages();
  }

  detached(): void {
    this.changeSubscriber.dispose();
  }

  setCurrentLanguage(language: Language) {
    this.language = language;
  }
}
