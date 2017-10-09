import {ApiRepository} from "common/repository/api-repository";
import {Resource} from "../resources/resource";
import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceRepository} from "../resources/resource-repository";

@autoinject
export class TaskFinder extends ApiRepository<Resource> {
  constructor(httpClient: HttpClient, private resourceRepository: ResourceRepository) {
    super(httpClient, 'tasks');
  }

  toEntity(data: Object): Promise<Resource> {
    return this.resourceRepository.toEntity(data);
  }
}
