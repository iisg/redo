import {Resource} from "./resource";
import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {ResourceMetadataSort} from "./resource-metadata-sort";
import {AbstractListQuery} from "./abstract-list-query";

export class ResourceListQuery extends AbstractListQuery<Resource> {
  constructor(httpClient: DeduplicatingHttpClient, endpoint: string, entitySerializer: EntitySerializer) {
    super(httpClient, endpoint, entitySerializer, 'Resource[]');
  }

  public filterByResourceKindIds(resourceKindIds: number | number[]): ResourceListQuery {
    if (!Array.isArray(resourceKindIds)) {
      resourceKindIds = [resourceKindIds as number];
    }
    if (!this.params.resourceKinds) {
      this.params.resourceKinds = [];
    }
    (resourceKindIds as number[]).forEach(rkId => this.params.resourceKinds.push(rkId));
    return this;
  }

  public filterByResourceClasses(resourceClasses: string | string[]): ResourceListQuery {
    if (!Array.isArray(resourceClasses)) {
      resourceClasses = [resourceClasses as string];
    }
    if (!this.params.resourceClasses) {
      this.params.resourceClasses = [];
    }
    (resourceClasses as string[]).forEach(resourceClass => this.params.resourceClasses.push(resourceClass));
    return this;
  }

  public filterByParentId(parentId: number): ResourceListQuery {
    this.params.parentId = parentId;
    return this;
  }

  public filterByWorkflowPlacesIds(workflowPlacesIds: string[]): ResourceListQuery {
    this.params.workflowPlacesIds = workflowPlacesIds;
    return this;
  }

  public sortByMetadataIds(sortBy: ResourceMetadataSort[]): ResourceListQuery {
    this.params.sortByIds = sortBy;
    return this;
  }

  filterByContents(contentsFilter: NumberMap<string>): ResourceListQuery {
    this.params.contentsFilter = contentsFilter;
    return this;
  }

  public onlyTopLevel(): ResourceListQuery {
    this.params.topLevel = true;
    return this;
  }
}
