import {ApiRepository} from "common/repository/api-repository";
import {Resource} from "../resources/resource";
import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {EntitySerializer} from "common/dto/entity-serializer";

@autoinject
export class TaskFinder extends ApiRepository<Resource> {
  constructor(httpClient: HttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, Resource, 'tasks');
  }
}
