import {autoinject} from "aurelia-dependency-injection";
import {HttpClient} from "aurelia-http-client";
import {CurrentUser} from "./current-user";
import {metricTime} from "../metrics/metrics-decorators";

@autoinject
export class CurrentUserFetcher {
  private httpClient: HttpClient;

  constructor(httpClient: HttpClient) {
    this.httpClient = httpClient;
  }

  @metricTime("fetching_user")
  fetch(): Promise<CurrentUser> {
    return this.httpClient.get('user/current')
      .then(response => response.content);
  }
}
