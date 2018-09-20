import {DeduplicatingHttpClient} from "../../common/http-client/deduplicating-http-client";
import {EntitySerializer} from "../../common/dto/entity-serializer";
import {ResourceKind} from "./resource-kind";
import {forOneMinute, cachedResponse} from "../../common/repository/cached-response";
import {ResourceSort} from "../../resources/resource-sort";

export class ResourceKindListQuery {
  private params: any = {};

  constructor(private httpClient: DeduplicatingHttpClient,
              private endpoint: string,
              private entitySerializer: EntitySerializer) {
  }

  public filterByIds(resourceKindIds: number | number[]): ResourceKindListQuery {
    if (!Array.isArray(resourceKindIds)) {
      resourceKindIds = [resourceKindIds as number];
    }
    if (!this.params.ids) {
      this.params.ids = [];
    }
    (resourceKindIds as number[]).forEach(rkId => this.params.ids.push(rkId));
    return this;
  }

  public filterByResourceClasses(resourceClasses: string | string[]): ResourceKindListQuery {
    if (!Array.isArray(resourceClasses)) {
      resourceClasses = [resourceClasses as string];
    }
    if (!this.params.resourceClasses) {
      this.params.resourceClasses = [];
    }
    (resourceClasses as string[]).forEach(resourceClass => this.params.resourceClasses.push(resourceClass));
    return this;
  }

  public filterByMetadataId(metadataId: number): ResourceKindListQuery {
    this.params.metadataId = metadataId;
    return this;
  }

  public sortByMetadataIds(sortBy: ResourceSort[]): ResourceKindListQuery {
    this.params.sortByIds = sortBy;
    return this;
  }

  public get(): Promise<ResourceKind[]> {
    return this.makeRequest(this.params);
  }

  @cachedResponse(forOneMinute())
  private makeRequest(params): Promise<ResourceKind[]> {
    return this.httpClient.get(this.endpoint, params)
      .then(response => this.entitySerializer.deserialize<ResourceKind[]>('ResourceKind[]', response.content));
  }
}
