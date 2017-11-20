import {ApiRepository} from "common/repository/api-repository";
import {Resource} from "resources/resource";
import {autoinject} from "aurelia-dependency-injection";
import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";

@autoinject
export class TaskFinder extends ApiRepository<Resource> {
  constructor(httpClient: DeduplicatingHttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, Resource, 'tasks');
  }
}
