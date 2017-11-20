import {ApiRepository} from "common/repository/api-repository";
import {autoinject} from "aurelia-dependency-injection";
import {UserRole} from "./user-role";
import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";

@autoinject
export class UserRoleRepository extends ApiRepository<UserRole> {
  constructor(httpClient: DeduplicatingHttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, UserRole, 'user-roles');
  }
}
