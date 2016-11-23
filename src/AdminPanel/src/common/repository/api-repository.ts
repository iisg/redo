import {HttpClient} from "aurelia-http-client";

export abstract class ApiRepository<T> {
  constructor(protected httpClient: HttpClient, protected endpoint: string) {
  }

  public getList(): Promise<T[]> {
    return this.httpClient.get(this.endpoint).then(response => response.content.map(item => this.toEntity(item)));
  }

  public post(entity: T): Promise<T> {
    return this.httpClient.post(this.endpoint, entity).then(response => this.toEntity(response.content));
  }

  abstract toEntity(data: Object): T;
}
