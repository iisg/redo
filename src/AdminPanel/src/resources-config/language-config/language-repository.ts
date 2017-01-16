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

  public update(updatedLanguage: Language): Promise<Language> {
    return this.patch(updatedLanguage, {
      flag: updatedLanguage.flag,
      name: updatedLanguage.name,
    });
  }

  @cachedResponse(900000)
  public getAvailableFlags(): Promise<Array<string>> {
    return this.httpClient.get('/flags.json').then((response) => response.content.available_flags);
  }

  protected oneEntityEndpoint(entity: number|string|Object): string {
    let languageCode = entity['code'] || entity;
    return `${this.endpoint}/${languageCode}`;
  }

  toEntity(data: Object): Language {
    return $.extend(new Language(), data);
  }
}

export class LanguagesChangedEvent {
}
