import {Language} from "./language";
import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";

@autoinject
export class LanguageRepository {
  private languageRequest: Promise<Language[]>;

  constructor(private httpClient: HttpClient, private eventAggregator: EventAggregator) {
  }

  public findAll(): Promise<Language[]> {
    if (!this.languageRequest) {
      this.languageRequest = this.httpClient.get('languages').then(response => {
        return response.content;
      });
    }
    return this.languageRequest;
  }

  public addNew(language: Language): Promise<Language> {
    return this.httpClient.post('languages', language).then((response) => {
      delete this.languageRequest;
      this.eventAggregator.publish(new LanguagesChangedEvent());
      return response.content;
    });
  }
}

export class LanguagesChangedEvent {
}
