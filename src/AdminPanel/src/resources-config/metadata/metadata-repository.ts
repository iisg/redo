import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "./metadata";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {EntitySerializer} from "common/dto/entity-serializer";
import {MetadataListQuery} from "./metadata-list-query";
import {ApiRepository} from "../../common/repository/api-repository";
import {cachedResponse, forOneMinute} from "../../common/repository/cached-response";
import {debouncePromise} from "../../common/utils/function-utils";
import {keyBy} from "lodash";

@autoinject
export class MetadataRepository extends ApiRepository<Metadata> {
  private idsToQuery = [];

  constructor(httpClient: DeduplicatingHttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, Metadata, 'metadata');
  }

  get(id: number | string, suppressError: boolean = false): Promise<Metadata> {
    if (this.idsToQuery.indexOf(id) === -1) {
      this.idsToQuery.push(id);
    }
    return this.fetchMetadata().then(metadataList => metadataList[id]);
  }

  private fetchMetadata = debouncePromise(() => {
    if (this.idsToQuery.length) {
      const query = this.getListQuery().filterByIds(this.idsToQuery);
      this.idsToQuery = [];
      return query.get().then(metadataList => keyBy(metadataList, 'id'));
    }
  }, 100);

  public getListQuery(): MetadataListQuery {
    return new MetadataListQuery(this.httpClient, this.endpoint, this.entitySerializer);
  }

  public saveChild(parentId: number, newChildMetadata: Metadata, baseId?: number) {
    let postChild = this.httpClient.post(
      this.oneEntityEndpoint(parentId) + '/metadata',
      {newChildMetadata: this.toBackend(newChildMetadata), baseId}
    ).then(response => this.toEntity(response.content));
    return baseId
      ? newChildMetadata.clearInheritedValues(this).then(() => postChild)
      : postChild;
  }

  public updateOrder(metadataList: Array<Metadata>, resourceClass: string): Promise<boolean> {
    return this.httpClient.put(this.endpoint, metadataList.map(metadata => metadata.id), {resourceClass})
      .then(response => response.content);
  }

  public update(updatedMetadata: Metadata): Promise<Metadata> {
    updatedMetadata = this.toBackend(updatedMetadata) as Metadata;
    return this.patch(updatedMetadata, {
      label: updatedMetadata.label,
      description: updatedMetadata.description,
      placeholder: updatedMetadata.placeholder,
      constraints: updatedMetadata.constraints,
      groupId: updatedMetadata.groupId,
      displayStrategy: updatedMetadata.displayStrategy,
      shownInBrief: updatedMetadata.shownInBrief,
      copyToChildResource: updatedMetadata.copyToChildResource
    });
  }

  @cachedResponse(forOneMinute())
  public getHierarchy(id: number): Promise<Metadata[]> {
    return this.httpClient.get(this.oneEntityEndpoint(id) + '/hierarchy').then(response => this.responseToEntities(response));
  }
}
