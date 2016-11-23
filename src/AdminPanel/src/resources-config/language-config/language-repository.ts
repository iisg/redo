import {Language} from "./language";
import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {ApiRepository} from "../../common/repository/api-repository";

@autoinject
export class LanguageRepository extends ApiRepository<Language> {
  private languageRequest: Promise<Language[]>;

  constructor(httpClient: HttpClient, private eventAggregator: EventAggregator) {
    super(httpClient, 'languages');
  }

  public getList(): Promise<Language[]> {
    if (!this.languageRequest) {
      this.languageRequest = super.getList();
    }
    return this.languageRequest;
  }

  public post(language: Language): Promise<Language> {
    return super.post(language).then((addedLanguage) => {
      this.languageRequest = undefined;
      this.eventAggregator.publish(new LanguagesChangedEvent());
      return addedLanguage;
    });
  }

  toEntity(data: Object): Language {
    return $.extend(new Language(), data);
  }
}

export class LanguagesChangedEvent {
}
