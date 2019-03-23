import {Mapper} from "common/dto/mappers";
import {autoinject} from "aurelia-dependency-injection";
import {PageResult} from "resources/page-result";
import {Resource} from "resources/resource";
import {EntitySerializer} from "common/dto/entity-serializer";

@autoinject
export class PageResultResourceMapper extends Mapper<PageResult<Resource>> {

  constructor(private entitySerializer: EntitySerializer) {
    super();
  }

  fromBackendValue(dto: any, currentEntity: PageResult<Resource>): Promise<PageResult<Resource>> {
    return this.entitySerializer.deserialize<PageResult<Resource>>('Resource[]', dto.results)
      .then(resources => {
        resources.page = dto.pageNumber;
        resources.total = dto.totalCount;
        return resources;
      });
  }

  toBackendValue(entity: PageResult<Resource>): any {
    throw new Error('toBackendValue() not implemented.');
  }

  protected clone(page: PageResult<Resource>): PageResult<Resource> {
    throw new Error('clone() not implemented.');
  }
}
