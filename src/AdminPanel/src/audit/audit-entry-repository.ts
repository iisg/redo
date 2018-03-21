import {ApiRepository} from "../common/repository/api-repository";
import {AuditEntry} from "./audit-entry";
import {DeduplicatingHttpClient} from "../common/http-client/deduplicating-http-client";
import {EntitySerializer} from "../common/dto/entity-serializer";
import {autoinject} from "aurelia-dependency-injection";
import {AuditEntryListQuery} from "./audit-entry-list-query";

@autoinject
export class AuditEntryRepository extends ApiRepository<AuditEntry> {
  constructor(httpClient: DeduplicatingHttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, AuditEntry, 'audit');
  }

  public getListQuery(): AuditEntryListQuery {
    return new AuditEntryListQuery(this.httpClient, this.endpoint, this.entitySerializer);
  }

  public getCommandNames(): Promise<string[]> {
    return this.httpClient.get('audit-commands').then(response => response.content);
  }
}
