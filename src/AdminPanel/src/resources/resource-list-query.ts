import {HttpClient} from "aurelia-http-client";
import {Resource} from "./resource";
import {ResourceRepository} from "./resource-repository";
import {deepCopy} from "common/utils/object-utils";
import {cachedResponse} from "common/repository/cached-response";

export class ResourceListQuery {

  private params: any = {};

  constructor(private httpClient: HttpClient, private resoruceRepository: ResourceRepository) {
  }

  public filterByResourceKindIds(resourceKindIds: number | number[]): ResourceListQuery {
    if (!Array.isArray(resourceKindIds)) {
      resourceKindIds = [resourceKindIds as number];
    }
    if (!this.params.resourceKinds) {
      this.params.resourceKinds = [];
    }
    (resourceKindIds as number[]).forEach(rkId => this.params.resourceKinds.push(rkId));
    return this;
  }

  public filterByResourceClasses(resourceClasses: string | string[]): ResourceListQuery {
    if (!Array.isArray(resourceClasses)) {
      resourceClasses = [resourceClasses as string];
    }
    if (!this.params.resourceClasses) {
      this.params.resourceClasses = [];
    }
    (resourceClasses as string[]).forEach(resourceClass => this.params.resourceClasses.push(resourceClass));
    return this;
  }

  public get(): Promise<Resource[]> {
    let params = deepCopy(this.params);
    for (let param in params) {
      if (params.hasOwnProperty(param)) {
        if (Array.isArray(params[param])) {
          params[param] = params[param].join(',');
        }
      }
    }
    return this.makeRequest(params);
  }

  @cachedResponse(20000)
  private makeRequest(params): Promise<Resource[]> {
    return this.httpClient
      .createRequest(this.resoruceRepository.endpoint)
      .asGet()
      .withParams(params)
      .send()
      .then(response => Promise.all(response.content.map(item => this.resoruceRepository.toEntity(item))));
  }
}
