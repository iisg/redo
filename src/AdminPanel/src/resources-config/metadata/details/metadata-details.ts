import {RoutableComponentActivate, RouteConfig, Router, NavigationInstruction} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {Metadata} from "../metadata";
import {MetadataRepository} from "../metadata-repository";
import {bindable} from "aurelia-templating";

@autoinject
export class MetadataDetails implements RoutableComponentActivate {
  metadataChildrenList: Metadata[];
  addFormOpened: boolean = false;
  @bindable
  metadata: Metadata;
  editing = false;
  private urlListener: Subscription;

  constructor(private metadataRepository: MetadataRepository, private i18n: I18N, private router: Router, private ea: EventAggregator) {
  }

  bind() {
    this.urlListener = this.ea.subscribe("router:navigation:success",
      (event: {instruction: NavigationInstruction}) => this.editing = event.instruction.queryParams.action == 'edit');
  }

  unbind() {
    this.urlListener.dispose();
  }

  activate(params: any, routeConfig: RouteConfig): void {
    this.metadata = undefined;
    this.metadataRepository.get(params.id).then(metadata => {
      this.metadata = metadata;
      routeConfig.navModel.setTitle(this.i18n.tr('Metadata') + ` #${metadata.id}`);
    });
  }

  addNewChildMetadata(parentId, baseID): Promise<Metadata> {
    return this.metadataRepository.saveChild(parentId, baseID).then(metadata => {
      this.addFormOpened = false;
      this.metadataChildrenList.unshift(metadata);
      return metadata;
    });
  }
}