import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceKind} from "./resource-kind";
import {deepCopy} from "../../common/utils/object-utils";
import {MetadataRepository} from "../metadata/metadata-repository";
import {ApiRepository} from "../../common/repository/api-repository";

@autoinject
export class ResourceKindRepository extends ApiRepository<ResourceKind> {
  constructor(httpClient: HttpClient, private metadataRepository: MetadataRepository) {
    super(httpClient, 'resource-kinds');
  }

  public post(resourceKind: ResourceKind): Promise<ResourceKind> {
    let toPost = deepCopy(resourceKind);
    for (let metadata of toPost.metadataList) {
      metadata.base_id = metadata.base.id;
      delete metadata.base;
    }
    return super.post(toPost);
  }

  public toEntity(data: Object): ResourceKind {
    let resourceKind = new ResourceKind;
    resourceKind.id = data['id'];
    resourceKind.label = data['label'];
    resourceKind.metadataList = data['metadata_list'].map(this.metadataRepository.toEntity);
    return resourceKind;
  }
}
