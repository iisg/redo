import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {autoinject} from "aurelia-dependency-injection";
import {MetadataRepository} from "resources-config/metadata/metadata-repository";
import {CustomEvent} from "common/events/custom-event";

@autoinject
export class ResourceListContentsFilter {
  @bindable(twoWay) contentsFilter: NumberMap<string>;
  rerenderYaml: () => void;

  newContentsFilter: AnyMap<string>;

  constructor(private element: Element, private metadataRepository: MetadataRepository) {
  }

  contentsFilterChanged() {
    this.replaceKeysInContents(this.contentsFilter, 'name').then(contentsFilter => {
      this.newContentsFilter = contentsFilter;
      if (this.rerenderYaml) {
        setTimeout(() => this.rerenderYaml());
      }
    });
  }

  submitOnCtrlEnter(event: KeyboardEvent) {
    if (event.ctrlKey && event.keyCode == 13) {
      this.updateFilter();
    }
  }

  updateFilter() {
    if (this.newContentsFilter) {
      this.replaceKeysInContents(this.newContentsFilter).then(contentsFilter => {
        this.contentsFilter = contentsFilter;
        setTimeout(() => this.element.dispatchEvent(CustomEvent.newInstance('filter')));
      });
    }
  }

  private replaceKeysInContents(contents: Object, useMetadataAttributeAsKey = 'id'): Promise<AnyMap<string>> {
    const metadataNamesOrIds = Object.keys(contents);
    const promises = [];
    for (const metadataNameOrId of metadataNamesOrIds) {
      promises.push(this.metadataRepository.get(metadataNameOrId));
    }
    return Promise.all(promises).then(metadataList => {
      const contentsFilter = {};
      for (let i = 0; i < metadataList.length; i++) {
        const metadataName = metadataNamesOrIds[i];
        const metadata = metadataList[i];
        contentsFilter[metadata[useMetadataAttributeAsKey]] = contents[metadataName];
      }
      return contentsFilter;
    });
  }
}
