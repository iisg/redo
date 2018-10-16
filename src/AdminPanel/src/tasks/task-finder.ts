import {autoinject} from "aurelia-dependency-injection";
import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {ApiRepository} from "common/repository/api-repository";
import {TasksCollection} from "./tasks-collection";

@autoinject
export class TaskFinder extends ApiRepository<TasksCollection> {
  constructor(httpClient: DeduplicatingHttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, TasksCollection, 'tasks');
  }
}
