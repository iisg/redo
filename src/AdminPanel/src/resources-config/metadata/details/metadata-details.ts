import {RoutableComponentActivate, RouteConfig, Router, NavigationInstruction} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {Metadata} from "../metadata";
import {MetadataRepository} from "../metadata-repository";

@autoinject
export class MetadataDetails implements RoutableComponentActivate {
  metadataChildrenList: Metadata[];
  metadata: Metadata;
  addFormOpened: boolean = false;
  editing = false;
  urlListener: Subscription;

  constructor(private metadataRepository: MetadataRepository, private i18n: I18N, private router: Router, private ea: EventAggregator) {
  }

  bind() {
    this.urlListener = this.ea.subscribe("router:navigation:success",
      (event: {instruction: NavigationInstruction}) => this.editing = event.instruction.queryParams.action == 'edit');
  }

  unbind() {
    this.urlListener.dispose();
  }

  async activate(params: any, routeConfig: RouteConfig) {
    this.metadata = await this.metadataRepository.get(params.id);
    routeConfig.navModel.setTitle(this.i18n.tr('Metadata') + ` #${this.metadata.id}`);
  }

  childMetadataSaved(savedMetadata: Metadata) {
    this.metadataChildrenList.unshift(savedMetadata);
    this.addFormOpened = false;
  }
}
