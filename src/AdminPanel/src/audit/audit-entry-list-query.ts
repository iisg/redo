import {AbstractListQuery} from "../resources/abstract-list-query";
import {AuditEntry} from "./audit-entry";
import {DeduplicatingHttpClient} from "../common/http-client/deduplicating-http-client";
import {EntitySerializer} from "../common/dto/entity-serializer";

export class AuditEntryListQuery extends AbstractListQuery<AuditEntry> {
  constructor(httpClient: DeduplicatingHttpClient, endpoint: string, entitySerializer: EntitySerializer) {
    super(httpClient, endpoint, entitySerializer, 'AuditEntry[]');
  }

  public filterByCommandNames(commandNames: string[]): this {
    this.params.commandNames = commandNames;
    return this;
  }
}
