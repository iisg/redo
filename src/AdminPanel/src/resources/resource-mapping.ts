import {AutoMapper} from "common/dto/auto-mapper";
import {Resource} from "./resource";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {autoinject} from "aurelia-dependency-injection";
import {AdvancedMapper} from "common/dto/mappers";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";
import {maps} from "common/dto/decorators";
import {MetadataValue} from "./metadata-value";

export class ResourceMapper extends AutoMapper<Resource> {

  static FILE_COUNTER = 0;

  fromBackendValue(dto: any, entity: Resource): Promise<Resource> {
    return super.fromBackendValue(dto, entity).then(resource => this.addContentsForEachMetadata(resource));
  }

  private addContentsForEachMetadata(resource: Resource): Resource {
    if (resource.kind === undefined) {
      return resource;
    }
    if (Object.keys(resource.contents).length === 1) {
      resource.isTeaser = true;
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
    const contents = this.contentsToBackend(resource.contents, formData);
    formData.append('id', resource.id + '');
    formData.append('kindId', resource.kind.id + '');
    formData.append('resourceClass', resource.resourceClass);
    formData.append('contents', JSON.stringify(contents));
    return formData;
  }

  private contentsToBackend(contents: NumberMap<MetadataValue[]>, formData: FormData): NumberMap<MetadataValue[]> {
    const copy: NumberMap<MetadataValue[]> = {};
    for (const metadataId in contents) {
      copy[metadataId] = [];
      if (contents[metadataId].length > 0) {
        for (let i in contents[metadataId]) {
          const originalItem = contents[metadataId][i];
          let newItem = new MetadataValue(originalItem.value);
          if (originalItem.value instanceof File) {
            newItem.value = this.wrapFileWithFormData(formData, originalItem.value, metadataId as any as number);
          }
          if (originalItem.submetadata) {
            newItem.submetadata = this.contentsToBackend(originalItem.submetadata, formData);
          }
          copy[metadataId].push(newItem);
        }
      }
    }
    return copy;
  }

  private wrapFileWithFormData(formData: FormData, file: File, metadataId: number): string {
    const fileIndex = ResourceMapper.FILE_COUNTER++;
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
