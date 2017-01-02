import {HttpClient} from "aurelia-http-client";

export abstract class ApiRepository<T> {
  constructor(protected httpClient: HttpClient, protected endpoint: string) {
  }

  public get(id: number|string): Promise<T> {
    return this.httpClient.get(this.oneEntityEndpoint(id)).then(response => this.toEntity(response.content));
  }

  public getList(): Promise<T[]> {
    return this.httpClient.get(this.endpoint).then(response => response.content.map(item => this.toEntity(item)));
  }

  public post(entity: T): Promise<T> {
    return this.httpClient.post(this.endpoint, this.toBackend(entity)).then(response => this.toEntity(response.content));
  }

  protected toBackend(entity: T): Object {
    return entity;
  }

  public patch(entity: T, patch: any) {
    return this.httpClient.patch(this.oneEntityEndpoint(entity), patch).then(response => this.toEntity(response.content));
  }

  private oneEntityEndpoint(entity: number|string|Object) {
    let id = entity['id'] || entity;
    return this.endpoint + `/${id}`;
  }

  abstract toEntity(data: Object): T;
}
