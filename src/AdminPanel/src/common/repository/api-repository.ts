import {HttpResponseMessage} from "aurelia-http-client";
import {suppressError as suppressErrorHeader} from "common/http-client/headers";
import {EntitySerializer} from "common/dto/entity-serializer";
import {EntityClass} from "../dto/contracts";
import {DeduplicatingHttpClient} from "../http-client/deduplicating-http-client";

export abstract class ApiRepository<T> {
  constructor(protected httpClient: DeduplicatingHttpClient,
              protected entitySerializer: EntitySerializer,
              protected entityClass: EntityClass<T>,
              protected endpoint: string) {
  }

  protected responseToEntities(response: HttpResponseMessage): Promise<T[]> {
    if (!response.content || !response.content.map) {
      throw new Error(`Response from ${response.requestMessage.url} should be an array`);
    }
    return Promise.all(response.content.map(item => this.toEntity(item)));
  }

  public get(id: number | string, suppressError: boolean = false): Promise<T> {
    const request = this.httpClient.createRequest(this.oneEntityEndpoint(id)).asGet();
    if (suppressError) {
      request.withHeader(suppressErrorHeader.name, suppressErrorHeader.value);
    }
    return request.send().then(response => this.toEntity(response.content));
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

  public remove(entity: T): Promise<any> { // 'delete' is a reserved word in ES6 and TS2
    return this.httpClient.delete(this.oneEntityEndpoint(entity));
  }

  protected toBackend(entity: T): Object {
    return this.entitySerializer.serialize(entity);
  }

  protected oneEntityEndpoint(entity: number | string | Object) {
    let id = entity['id'] || entity;
    return `${this.endpoint}/${id}`;
  }

  protected toEntity(data: Object): Promise<T> {
    return this.entitySerializer.deserialize(this.entityClass, data);
  }
}
