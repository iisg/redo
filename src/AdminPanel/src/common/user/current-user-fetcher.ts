import {autoinject} from "aurelia-dependency-injection";
import {HttpClient} from "aurelia-http-client";
import {CurrentUser} from "./current-user";

@autoinject
export class CurrentUserFetcher {
  private httpClient: HttpClient;

  constructor(httpClient: HttpClient) {
    this.httpClient = httpClient;
  }

  fetch(): Promise<CurrentUser> {
    return this.httpClient.get('user/current')
      .then(response => response.content);
  }
}
