import {Resource} from "./resource";
import {deepCopy} from "common/utils/object-utils";
import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {PageResult} from "./page-result";

export class ResourceListQuery {
  private params: any = {};

  constructor(private httpClient: DeduplicatingHttpClient, private endpoint: string, private entitySerializer: EntitySerializer) {
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

  public filterByParentId(parentId: number): ResourceListQuery {
    this.params.parentId = parentId;
    return this;
  }

  public setPage(page: number): ResourceListQuery {
    this.params.page = page;
    return this;
  }

  public setResultsPerPage(resultsPerPage: number): ResourceListQuery {
    this.params.resultsPerPage = resultsPerPage;
    return this;
  }

  public onlyTopLevel(): ResourceListQuery {
    this.params.topLevel = true;
    return this;
  }

  public get(): Promise<PageResult<Resource>> {
    const params = deepCopy(this.params);
    for (let param in params) {
      if (params.hasOwnProperty(param)) {
        if (Array.isArray(params[param])) {
          params[param] = params[param].join(',');
        }
      }
    }
    return this.makeRequest(params);
  }

  private makeRequest(params): Promise<PageResult<Resource>> {
    return this.httpClient.get(this.endpoint, params)
      .then(response => {
        const total = +response.headers.get('pk_total');
        const page = +response.headers.get('pk_page');
        return this.entitySerializer.deserialize<PageResult<Resource>>('Resource[]', response.content).then(resources => {
          resources.total = total;
          resources.page = page;
          return resources;
        });
      });
  }
}
