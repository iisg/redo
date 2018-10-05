import {Resource} from "./resource";
import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {cachedResponse, forSeconds} from "../common/repository/cached-response";
import {TreeResult} from 'resources/tree-result';
import {HttpClient} from "aurelia-http-client";

export class ResourceTreeQuery {
  private params: any = {};

  constructor(private httpClient: DeduplicatingHttpClient, private endpoint: string, private entitySerializer: EntitySerializer) {
  }

  public forRootId(rootId: number): ResourceTreeQuery {
    this.params.rootId = rootId;
    return this;
  }

  public includeWithinDepth(depth: number): ResourceTreeQuery {
    this.params.depth = depth;
    return this;
  }

  public filterBySiblingsNumber(siblings: number): ResourceTreeQuery {
    this.params.siblings = siblings;
    return this;
  }

  public filterByResourceKindIds(resourceKindIds: number | number[]): ResourceTreeQuery {
    if (!Array.isArray(resourceKindIds)) {
      resourceKindIds = [resourceKindIds as number];
    }
    if (!this.params.resourceKinds) {
      this.params.resourceKinds = [];
    }
    (resourceKindIds as number[]).forEach(rkId => this.params.resourceKinds.push(rkId));
    return this;
  }

  public filterByResourceClasses(resourceClasses: string | string[]): ResourceTreeQuery {
    if (!Array.isArray(resourceClasses)) {
      resourceClasses = [resourceClasses as string];
    }
    if (!this.params.resourceClasses) {
      this.params.resourceClasses = [];
    }
    (resourceClasses as string[]).forEach(resourceClass => this.params.resourceClasses.push(resourceClass));
    return this;
  }

  public filterByContents(contentsFilter: NumberMap<string>): ResourceTreeQuery {
    this.params.contentsFilter = contentsFilter;
    return this;
  }

  public oneMoreElements(): ResourceTreeQuery {
    this.params.oneMoreElements = true;
    return this;
  }

  public setCurrentPageNumber(currentPageNumber: number): ResourceTreeQuery {
    this.params.page = currentPageNumber;
    return this;
  }

  public setResultsPerPage(resultsPerPage: number): ResourceTreeQuery {
    this.params.resultsPerPage = resultsPerPage;
    return this;
  }

  public get(treeQueryUrl?: string): Promise<TreeResult<Resource>> {
    return this.makeRequest(treeQueryUrl, this.params);
  }

  @cachedResponse(forSeconds(10))
  private makeRequest(treeQueryUrl: string, params): Promise<TreeResult<Resource>> {
    const endpoint = treeQueryUrl ? treeQueryUrl : this.endpoint;
    const http = treeQueryUrl ? new HttpClient() : this.httpClient;
    return http.createRequest(endpoint)
      .asGet()
      .withParams(params)
      .send()
      .then(response => {
        return this.entitySerializer.deserialize<Array<Resource>>('Resource[]', Object.values(response.content.treeContents))
          .then(resources => {
            return {
              tree: resources,
              matching: response.content.matchingIds,
              page: response.content.topLevelPageNumber
            };
          });
      });
  }
}
