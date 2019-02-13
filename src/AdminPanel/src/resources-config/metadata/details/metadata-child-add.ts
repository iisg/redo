import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {MetadataForm} from "resources-config/metadata/metadata-form";
import {Metadata} from "../metadata";
import {MetadataRepository} from "../metadata-repository";
import {EventAggregator} from "aurelia-event-aggregator";

@autoinject
export class MetadataChildAdd implements ComponentAttached, ComponentDetached {
  @bindable parentMetadata: Metadata;
  @bindable resourceClass: string;
  @bindable saved: (value: { savedMetadata: Metadata }) => any;
  @bindable cancel: () => void;
  metadataForm: MetadataForm;
  metadataList: Metadata[];
  parentMetadataChildren: Metadata[];
  baseMetadata: Metadata;
  addingNewSubmetadataKind: boolean;
  private notSelected: (metadata: Metadata) => boolean;

  constructor(private metadataRepository: MetadataRepository, private eventAggregator: EventAggregator) {
  }

  attached() {
    Promise.all([
      this.metadataRepository.getListQuery()
        .filterByResourceClasses(this.resourceClass)
        .onlyTopLevel()
        .get(),
      this.metadataRepository.getListQuery().filterByParentId(this.parentMetadata.id).get()
    ]).then(results => {
      this.metadataList = results[0];
      this.parentMetadataChildren = results[1];
      this.notSelected = (metadata: Metadata) => {
        return this.parentMetadataChildren.map(m => m.baseId).indexOf(metadata.id) === -1;
      };
    });
    this.eventAggregator.publish('submetadataFormOpened', true);
  }

  detached() {
    this.eventAggregator.publish('submetadataFormOpened', false);
  }

  toggleAddingNewSubmetadataKind() {
    this.metadataForm.changeLossPreventer.canLeaveView().then(canLeaveView => {
      if (canLeaveView) {
        this.addingNewSubmetadataKind = !this.addingNewSubmetadataKind;
      }
    });
  }

  addChildMetadata(parentId: number, baseId: number, newChildMetadata: Metadata): Promise<Metadata> {
    return this.metadataRepository.saveChild(parentId, newChildMetadata, baseId)
      .then(metadata => this.saved({savedMetadata: metadata}))
      .then((metadata) => {
        this.baseMetadata = undefined;
        this.eventAggregator.publish('submetadataFormOpened', false);
        return metadata;
      });
  }

  createChildMetadata(parentId: number, newChildMetadata: Metadata): Promise<Metadata> {
    return this.metadataRepository.saveChild(parentId, newChildMetadata)
      .then(metadata => {
        this.saved({savedMetadata: metadata});
        this.eventAggregator.publish('submetadataFormOpened', false);
        return metadata;
      });
  }
}
