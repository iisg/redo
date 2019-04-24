import {BindingEngine, Disposable} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {MetadataValue} from "resources/metadata-value";

@autoinject
export class AuditUserChooser {
  @bindable(twoWay) selectedUsersIds: number[];
  userIdsMetadata: MetadataValue[] = [];
  private userIdsSubscription: Disposable;

  constructor(private bindingEngine: BindingEngine) {
  }

  attached() {
    this.synchronizeIds();
  }

  removeUserFilter(id) {
    this.selectedUsersIds = this.selectedUsersIds.filter(userId => userId != id);
    this.synchronizeIds();
  }

  private synchronizeIds() {
    this.userIdsMetadata = this.selectedUsersIds.map(id => new MetadataValue(id));
    this.watchUserIdsChanges();
  }

  private watchUserIdsChanges() {
    if (this.userIdsSubscription) {
      this.userIdsSubscription.dispose();
    }
    this.userIdsSubscription = this.bindingEngine
      .collectionObserver(this.userIdsMetadata)
      .subscribe(() => this.selectedUsersIds = this.userIdsMetadata.map(v => v.value));
  }

  detached() {
    this.userIdsSubscription.dispose();
  }
}
