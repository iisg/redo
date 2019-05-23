import {bindable} from "aurelia-templating";
import {Resource} from "../../../resource";
import {Metadata} from "../../../../resources-config/metadata/metadata";
import {autoinject} from "aurelia-dependency-injection";
import {MetadataValue} from "../../../metadata-value";
import {ChangeEvent} from "../../../../common/events/change-event";
import {I18N} from "aurelia-i18n";

@autoinject
export class ResourceFileManager {
  @bindable resource: Resource;
  @bindable metadata: Metadata;
  @bindable skipValidation: boolean;

  private readonly listener;
  private fileManagerWindow;

  constructor(private element: Element, private i18n: I18N) {
    this.listener = (event) => {
      if (event.data.command == 'addFileMetadata') {
        if (this.metadata && this.metadata.id == event.data.metadataId) {
          event.data.files.forEach(file => {
            const extensionAllowed = !this.metadata.constraints.allowedFileExtensions
              || this.metadata.constraints.allowedFileExtensions.includes(file.split('.').pop());
            if (this.skipValidation || extensionAllowed) {
              this.resource.contents[this.metadata.id].push(new MetadataValue(file));
              this.respond('success');
            } else {
              this.respond('error', this.i18n.tr('Forbidden file extension'));
            }
          });
          this.element.dispatchEvent(ChangeEvent.newInstance());
        }
      }
    };
  }

  respond(result: string, message: string = '') {
    this.fileManagerWindow.postMessage(({command: 'showToast', result: result, message: message}), '*');
  }

  openFileManager() {
    const params = {};
    if (this.metadata) {
      params['metadataId'] = this.metadata.id;
    }
    if (this.skipValidation) {
      params['god'] = 1;
    }
    const uri = `/api/resources/${this.resource.id}/file-manager.html?${$.param(params)}`;
    this.fileManagerWindow = window.open(uri, 'popup', 'width=800,height=420');
  }

  attached() {
    window.addEventListener("message", this.listener);
  }

  detached() {
    window.removeEventListener("message", this.listener);
  }
}
