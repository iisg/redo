import {autoinject} from "aurelia-dependency-injection";
import {DialogComponentActivate, DialogController} from "aurelia-dialog";
import {keys, pullAll, toInteger} from "lodash";
import {MetadataRepository} from "resources-config/metadata/metadata-repository";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";
import {Resource} from "resources/resource";
import {AuditEntry} from "../audit-entry";

@autoinject
export class AuditEntryDetailsResourceContentsDialog implements DialogComponentActivate<AuditEntryDetailsResourceContentsDialogModel> {
  entry: AuditEntry;
  beforeChangeResource: Resource;
  afterChangeResource: Resource;
  resources: Resource[] = [];
  resourceLabels: string[] = [];
  private isLoaded: boolean;

  constructor(public dialogController: DialogController,
              private metadataRepository: MetadataRepository,
              private resourceKindRepository: ResourceKindRepository) {
  }

  async activate(model: AuditEntryDetailsResourceContentsDialogModel) {
    this.isLoaded = false;
    if (model.beforeChangeResource) {
      this.beforeChangeResource = await this.prepareResource(model.beforeChangeResource);
      this.resources.push(this.beforeChangeResource);
      this.resourceLabels.push('Before');
    }
    if (model.afterChangeResource) {
      this.afterChangeResource = await this.prepareResource(model.afterChangeResource);
      this.resources.push(this.afterChangeResource);
      this.resourceLabels.push('After');
    }
    this.entry = model.entry;
    this.isLoaded = true;
  }

  private async prepareResource(resource: Resource): Promise<Resource> {
    if (!resource.kind.metadataList.length) {
      const metadataIdsFromContents = keys(resource.contents).map(toInteger);
      try {
        resource.kind = await this.resourceKindRepository.get(resource.kind.id, true);
        const metadataIdsFromRK = resource.kind.metadataList.map(m => m.id);
        pullAll(metadataIdsFromContents, metadataIdsFromRK);
      } catch (e) {
      }
      if (metadataIdsFromContents.length) {
        const metadataListBasedOnContents = await this.metadataRepository.getListQuery().filterByIds(metadataIdsFromContents).get();
        resource.kind.metadataList = resource.kind.metadataList.concat(metadataListBasedOnContents);
      }
    }
    return resource;
  }
}

export interface AuditEntryDetailsResourceContentsDialogModel {
  beforeChangeResource: Resource;
  afterChangeResource: Resource;
  entry: AuditEntry;
}
