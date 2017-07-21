import {HttpClient, HttpResponseMessage} from "aurelia-http-client";

export abstract class ApiRepository<T> {
  constructor(protected httpClient: HttpClient, protected endpoint: string) {
  }

  protected responseToEntities(response: HttpResponseMessage): Promise<T[]> {
    if (!response.content || !response.content.map) {
      throw new Error(`Response from ${response.requestMessage.url} should be an array`);
    }
    return Promise.all(response.content.map(item => this.toEntity(item)));
  }

  public get(id: number|string): Promise<T> {
    return this.httpClient.get(this.oneEntityEndpoint(id)).then(response => this.toEntity(response.content));
  }

  public getList(): Promise<T[]> {
    return this.httpClient.get(this.endpoint).then(response => this.responseToEntities(response));
  }

  public post(entity: T): Promise<T> {
    return this.httpClient.post(this.endpoint, this.toBackend(entity)).then(response => this.toEntity(response.content));
  }

  public patch(entity: T, patch: any): Promise<T> {
    return this.httpClient.patch(this.oneEntityEndpoint(entity), patch).then(response => this.toEntity(response.content));
  }

  public put(entity: T) {
    return this.httpClient.put(this.oneEntityEndpoint(entity), this.toBackend(entity)).then(response => this.toEntity(response.content));
  }

  protected toBackend(entity: T): Object {
    return entity;
  }

  protected oneEntityEndpoint(entity: number|string|Object) {
    let id = entity['id'] || entity;
    return `${this.endpoint}/${id}`;
  }

  abstract toEntity(data: Object): T|Promise<T>;
}
