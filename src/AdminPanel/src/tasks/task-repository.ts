import {autoinject} from "aurelia-dependency-injection";
import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {ApiRepository} from "common/repository/api-repository";
import {TaskCollection} from "./task-collection";
import {TaskCollectionsQuery} from "./task-collection-query";

@autoinject
export class TaskRepository extends ApiRepository<TaskCollection> {
  constructor(httpClient: DeduplicatingHttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, TaskCollection, 'tasks');
  }

  public getCollectionsQuery(): TaskCollectionsQuery {
    return new TaskCollectionsQuery(this.httpClient, this.endpoint, this.entitySerializer);
  }
}
