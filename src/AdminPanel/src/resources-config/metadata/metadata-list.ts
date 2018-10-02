import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";
import {Metadata} from "./metadata";
import {MetadataRepository} from "./metadata-repository";
import {Router} from "aurelia-router";
import {ContextResourceClass} from "../../resources/context/context-resource-class";
import {SystemMetadata} from "./system-metadata";

@autoinject
export class MetadataList {
  @bindable parentMetadata: Metadata;
  @bindable resourceClass: string;
  metadataList: Metadata[];
  addFormOpened: boolean = false;
  progressBar: boolean;

  constructor(private metadataRepository: MetadataRepository, private contextResourceClass: ContextResourceClass, private router: Router) {
  }

  activate(params: any) {
    this.resourceClass = params.resourceClass;
    this.contextResourceClass.setCurrent(this.resourceClass);
    if (this.metadataList) {
      this.metadataList = [];
    }
    this.fetchMetadata();
  }

  parentMetadataChanged() {
    this.fetchMetadata();
  }

  private async fetchMetadata() {
    this.progressBar = true;
    this.metadataList = undefined;
    let query = this.metadataRepository.getListQuery();
    query = this.parentMetadata
      ? query.filterByParentId(this.parentMetadata.id)
      : query.filterByResourceClasses(this.resourceClass).onlyTopLevel().addSystemMetadataIds(SystemMetadata.REPRODUCTOR.id);
    this.metadataList = await query.get().then(metadataList => this.moveSystemMetadataToTheEnd(metadataList));
    this.progressBar = false;
  }

  isDragHandle(data: { evt: MouseEvent }) {
    return $(data.evt.target).is('.drag-handle') || $(data.evt.target).parents('.drag-handle').length > 0;
  }

  onOrderChanged() {
    this.metadataList = this.moveSystemMetadataToTheEnd(this.metadataList);
    this.metadataRepository.updateOrder(this.metadataList, this.resourceClass);
  }

  moveSystemMetadataToTheEnd(metadataList: Metadata[]) {
    const metadataListCopy = metadataList.slice();
    const systemMetadataList = [];
    for (let index = metadataListCopy.length - 1; index >= 0; index--) {
      if (metadataListCopy[index].id < 0) {
        systemMetadataList.push(metadataListCopy.splice(index, 1)[0]);
      }
    }
    return metadataListCopy.concat(systemMetadataList.sort());
  }

  addNewMetadata(newMetadata: Metadata): Promise<any> {
    newMetadata.resourceClass = this.resourceClass;
    return this.metadataRepository.post(newMetadata)
      .then(metadata => this.metadataAdded(metadata));
  }

  metadataAdded(newMetadata: Metadata) {
    this.addFormOpened = false;
    this.router.navigateToRoute('metadata/details', {id: newMetadata.id});
  }
}
