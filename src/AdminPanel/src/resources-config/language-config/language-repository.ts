import {Language} from "./language";
import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {ApiRepository} from "../../common/repository/api-repository";
import {cachedResponse, clearCachedResponse} from "../../common/repository/cached-response";

@autoinject
export class LanguageRepository extends ApiRepository<Language> {
  constructor(httpClient: HttpClient, private eventAggregator: EventAggregator) {
    super(httpClient, 'languages');
  }

  @cachedResponse()
  public getList(): Promise<Language[]> {
    return super.getList();
  }

  public post(language: Language): Promise<Language> {
    return super.post(language).then((addedLanguage) => {
      clearCachedResponse(this.getList);
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
