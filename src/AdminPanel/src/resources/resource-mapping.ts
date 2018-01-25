import {AutoMapper} from "common/dto/auto-mapper";
import {Resource} from "./resource";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {autoinject} from "aurelia-dependency-injection";
import {AdvancedMapper} from "common/dto/mappers";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";
import {maps} from "common/dto/decorators";

export class ResourceMapper extends AutoMapper<Resource> {
  fromBackendValue(dto: any, entity: Resource): Promise<Resource> {
    return super.fromBackendValue(dto, entity).then(resource => this.addContentsForEachMetadata(resource));
  }

  private addContentsForEachMetadata(resource: Resource): Resource {
    if (resource.kind === undefined) {
      return resource;
    }
    for (const metadata of resource.kind.metadataList) {
      if (!(metadata.id in resource.contents)) {
        resource.contents[metadata.id] = [];
      }
    }
    return resource;
  }

  toBackendValue(resource: Resource): Object {
    const formData = new FormData();
    const resourceCopy = new Resource();
    let fileCounter = 0;

    for (let metadataId in resource.contents) {
      if (resource.contents[metadataId].length > 0) {
        resourceCopy.contents[metadataId] = resource.contents[metadataId].map(item => {
          if (item.value instanceof File) {
            fileCounter++;
            item.value = this.wrapFileWithFormData(formData, item.value, metadataId as any as number, fileCounter);
          }
          return item;
        });
      } else {
        resourceCopy.contents[metadataId] = [];
      }
    }

    formData.append('id', resource.id + '');
    formData.append('kindId', resource.kind.id + '');
    formData.append('resourceClass', resource.resourceClass);
    formData.append('contents', JSON.stringify(resourceCopy.contents));
    return formData;
  }

  private wrapFileWithFormData(formData: FormData, file: File, metadataId: number, fileIndex: number): string {
    const resourceName = `metadata${metadataId}_file${fileIndex}`;
    formData.append(resourceName, file, file.name);
    return resourceName;
  }
}

@autoinject
@maps('ResourceKindId')
export class ResourceKindIdMapper extends AdvancedMapper<ResourceKind> {
  constructor(private resourceKindRepository: ResourceKindRepository, private autoMapper: AutoMapper<ResourceKind>) {
    super();
  }

  fromBackendProperty(key: string, dto: Object, resource: Object): Promise<ResourceKind> {
    const dtoKey = key + 'Id';
    const resourceKindId = dto[dtoKey];
    return this.resourceKindRepository.get(resourceKindId);
  }

  toBackendProperty(key: string, resource: Resource, dto: Object): void {
    const dtoKey = key + 'Id';
    dto[dtoKey] = resource.kind.id;
  }

  clone(resourceKind: ResourceKind): ResourceKind {
    return this.autoMapper.nullSafeClone(resourceKind);
  }
}
