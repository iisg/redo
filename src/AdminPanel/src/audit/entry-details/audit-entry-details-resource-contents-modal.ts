import {Resource} from "../../resources/resource";
import {AuditEntry} from "../audit-entry";
import {DialogController} from "aurelia-dialog";
import {autoinject} from "aurelia-dependency-injection";
import {MetadataRepository} from "../../resources-config/metadata/metadata-repository";
import {ResourceKindRepository} from "../../resources-config/resource-kind/resource-kind-repository";
import {keys, pullAll, toInteger} from "lodash";

@autoinject
export class AuditEntryDetailsResourceContentsModal {
  resource: Resource;
  entry: AuditEntry;

  constructor(private dialogController: DialogController,
              private metadataRepository: MetadataRepository,
              private resourceKindRepository: ResourceKindRepository) {
  }

  async activate(model) {
    if (!model.resource.kind.metadataList.length) {
      const metadataIdsFromContents = keys(model.resource.contents).map(toInteger);
      try {
        model.resource.kind = await this.resourceKindRepository.get(model.resource.kind.id, true);
        const metadataIdsFromRK = model.resource.kind.metadataList.map(m => m.id);
        pullAll(metadataIdsFromContents, metadataIdsFromRK);
      } catch (e) {
      }
      if (metadataIdsFromContents.length) {
        const metadataListBasedOnContents = await this.metadataRepository.getListQuery().filterByIds(metadataIdsFromContents).get();
        model.resource.kind.metadataList = model.resource.kind.metadataList.concat(metadataListBasedOnContents);
      }
    }
    this.resource = model.resource;
    this.entry = model.entry;
  }
}
