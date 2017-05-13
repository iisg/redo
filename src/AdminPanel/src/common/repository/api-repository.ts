import {HttpClient} from "aurelia-http-client";

export abstract class ApiRepository<T> {
  constructor(protected httpClient: HttpClient, protected endpoint: string) {
  }

  public get(id: number|string): Promise<T> {
    return this.httpClient.get(this.oneEntityEndpoint(id)).then(response => this.toEntity(response.content));
  }

  public getList(): Promise<T[]> {
    return this.httpClient.get(this.endpoint).then(response => {
      if (!response.content || !response.content.map) {
        throw new Error(`Response from ${this.endpoint} getList endpoint should be an array, not an object or something else.`);
      }
      return Promise.all(response.content.map(item => this.toEntity(item)));
    });
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
