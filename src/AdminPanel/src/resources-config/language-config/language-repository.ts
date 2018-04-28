import {Language} from "./language";
import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {ApiRepository} from "common/repository/api-repository";
import {cachedResponse, forSeconds} from "common/repository/cached-response";
import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";

@autoinject
export class LanguageRepository extends ApiRepository<Language> {
  constructor(httpClient: DeduplicatingHttpClient, entitySerializer: EntitySerializer, private eventAggregator: EventAggregator) {
    super(httpClient, entitySerializer, Language, 'languages');
  }

  @cachedResponse()
  public getList(): Promise<Language[]> {
    return super.getList();
  }

  public post(language: Language): Promise<Language> {
    return super.post(language).then(v => this.dispatchChangedEvent(v));
  }

  public patch(language: Language, patch: any): Promise<Language> {
    return super.patch(language, patch).then(v => this.dispatchChangedEvent(v));
  }

  public remove(entity: Language): Promise<void> {
    return super.remove(entity).then(v => this.dispatchChangedEvent(v));
  }

  private dispatchChangedEvent<T>(arg: T): T {
    this.eventAggregator.publish(new LanguagesChangedEvent());
    return arg;
  }

  public update(updatedLanguage: Language): Promise<Language> {
    const languageData = this.toBackend(updatedLanguage);
    return this.patch(languageData as Language, {
      flag: updatedLanguage.flag,
      name: updatedLanguage.name,
    });
  }

  @cachedResponse(forSeconds(90))
  public getAvailableFlags(): Promise<Array<string>> {
    return new HttpClient().get('/files/flags.json')
      .then(response => response.content.available_flags)
      .then(flags => flags.sort());
  }

  protected oneEntityEndpoint(entity: number | string | Object): string {
    let languageCode = entity['code'] || entity;
    return `${this.endpoint}/${languageCode}`;
  }
}

export class LanguagesChangedEvent {
}
