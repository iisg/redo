import {DeduplicatingHttpClient} from "../../common/http-client/deduplicating-http-client";
import {EntitySerializer} from "../../common/dto/entity-serializer";
import {ResourceKind} from "./resource-kind";
import {forOneMinute, cachedResponse} from "../../common/repository/cached-response";

export class ResourceKindListQuery {
  private params: any = {};

  constructor(private httpClient: DeduplicatingHttpClient, private endpoint: string, private entitySerializer: EntitySerializer) {
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

  public get(): Promise<ResourceKind[]> {
    return this.makeRequest(this.params);
  }

  @cachedResponse(forOneMinute())
  private makeRequest(params): Promise<ResourceKind[]> {
    return this.httpClient.get(this.endpoint, params)
      .then(response => this.entitySerializer.deserialize<ResourceKind[]>('ResourceKind[]', response.content));
  }
}
