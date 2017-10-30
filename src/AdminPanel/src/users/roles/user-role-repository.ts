import {ApiRepository} from "common/repository/api-repository";
import {autoinject} from "aurelia-dependency-injection";
import {HttpClient} from "aurelia-http-client";
import {UserRole} from "./user-role";
import {EntitySerializer} from "common/dto/entity-serializer";

@autoinject
export class UserRoleRepository extends ApiRepository<UserRole> {
  constructor(httpClient: HttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, UserRole, 'user-roles');
  }
}
