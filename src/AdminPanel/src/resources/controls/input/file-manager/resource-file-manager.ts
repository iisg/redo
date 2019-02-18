import {bindable} from "aurelia-templating";
import {Resource} from "../../../resource";
import {Metadata} from "../../../../resources-config/metadata/metadata";
import {autoinject} from "aurelia-dependency-injection";
import {MetadataValue} from "../../../metadata-value";
import {ChangeEvent} from "../../../../common/events/change-event";

@autoinject
export class ResourceFileManager {
  @bindable resource: Resource;
  @bindable metadata: Metadata;
  @bindable skipValidation: boolean;

  private readonly listener;

  constructor(private element: Element) {
    this.listener = (event) => {
      if (event.data.command == 'addFileMetadata') {
        if (this.metadata && this.metadata.id == event.data.metadataId) {
          event.data.files.forEach(file => this.resource.contents[this.metadata.id].push(new MetadataValue(file)));
          this.element.dispatchEvent(ChangeEvent.newInstance());
        }
      }
    };
  }

  openFileManager() {
    const params = {};
    if (this.metadata) {
      params['metadataId'] = this.metadata.id;
    }
    if (this.skipValidation) {
      params['god'] = 1;
    }
    window.open(`/api/resources/${this.resource.id}/file-manager.html?${$.param(params)}`, 'popup', 'width=800,height=420');
  }

  attached() {
    window.addEventListener("message", this.listener);
  }

  detached() {
    window.removeEventListener("message", this.listener);
  }
}
