import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {TaskCollection, TaskStatus} from "./task-collection";
import {cachedResponse, forSeconds} from "common/repository/cached-response";
import {ResourceListQuery} from "resources/resource-list-query";

export class TaskCollectionsQuery {
  private params: any = {};

  constructor(private httpClient: DeduplicatingHttpClient, private endpoint: string, private entitySerializer: EntitySerializer) {
  }

  public onlyQueriedCollections(): this {
    this.params.onlyQueriedCollections = true;
    return this;
  }

  public static getSingleCollectionQuery(): ResourceListQuery {
    return new ResourceListQuery(undefined, undefined, undefined);
  }

  public addSingleCollectionQuery(resourceClass: string, taskStatus: TaskStatus, query: ResourceListQuery): this {
    if (!this.params.queries) {
      this.params.queries = {};
    }
    if (!this.params.queries[resourceClass]) {
      this.params.queries[resourceClass] = {};
    }
    this.params.queries[resourceClass][taskStatus] = query.getParams();
    return this;
  }

  public get(): Promise<TaskCollection[]> {
    return this.makeRequest(this.params);
  }

  @cachedResponse(forSeconds(10))
  private makeRequest(params): Promise<TaskCollection[]> {
    return this.httpClient.createRequest(this.endpoint)
      .asGet()
      .withParams(params)
      .send()
      .then(response => {
        return this.entitySerializer.deserialize<Array<TaskCollection>>('TaskCollection[]', Object.values(response.content));
      });
  }
}