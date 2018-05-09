import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {Metadata} from "./metadata";
import {cachedResponse, forOneMinute} from "../../common/repository/cached-response";

export class MetadataListQuery {
  private params: any = {};

  constructor(private httpClient: DeduplicatingHttpClient, private endpoint: string, private entitySerializer: EntitySerializer) {
  }

  public filterByResourceClasses(resourceClasses: string | string[]): MetadataListQuery {
    if (!Array.isArray(resourceClasses)) {
      resourceClasses = [resourceClasses as string];
    }
    if (!this.params.resourceClasses) {
      this.params.resourceClasses = [];
    }
    (resourceClasses as string[]).forEach(resourceClass => this.params.resourceClasses.push(resourceClass));
    return this;
  }

  public filterByControls(metadataControls: string | string[]): MetadataListQuery {
    if (!Array.isArray(metadataControls)) {
      metadataControls = [metadataControls as string];
    }
    if (!this.params.controls) {
      this.params.controls = [];
    }
    (metadataControls as string[]).forEach(resourceClass => this.params.controls.push(resourceClass));
    return this;
  }

  public filterByParentId(parentId: number): MetadataListQuery {
    this.params.parentId = parentId;
    return this;
  }

  public onlyTopLevel(): MetadataListQuery {
    this.params.topLevel = true;
    return this;
  }

  public get(): Promise<Metadata[]> {
    return this.makeRequest(this.params);
  }

  @cachedResponse(forOneMinute())
  private makeRequest(params): Promise<Metadata[]> {
    return this.httpClient.get(this.endpoint, params)
      .then(response => this.entitySerializer.deserialize<Metadata[]>('Metadata[]', response.content));
  }
}
