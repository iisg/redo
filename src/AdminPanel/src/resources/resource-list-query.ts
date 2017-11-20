import {Resource} from "./resource";
import {deepCopy} from "common/utils/object-utils";
import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";

export class ResourceListQuery {
  private params: any = {};

  constructor(private httpClient: DeduplicatingHttpClient, private endpoint: string, private entitySerializer: EntitySerializer) {
  }

  public filterByResourceKindIds(resourceKindIds: number|number[]): ResourceListQuery {
    if (!Array.isArray(resourceKindIds)) {
      resourceKindIds = [resourceKindIds as number];
    }
    if (!this.params.resourceKinds) {
      this.params.resourceKinds = [];
    }
    (resourceKindIds as number[]).forEach(rkId => this.params.resourceKinds.push(rkId));
    return this;
  }

  public filterByResourceClasses(resourceClasses: string|string[]): ResourceListQuery {
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

  private makeRequest(params): Promise<Resource[]> {
    return this.httpClient.get(this.endpoint, params)
      .then(response => Promise.all(response.content.map(item => this.entitySerializer.deserialize(Resource, item))));
  }
}
