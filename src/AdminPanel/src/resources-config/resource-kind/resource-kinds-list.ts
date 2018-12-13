import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {I18N} from "aurelia-i18n";
import {Router} from "aurelia-router";
import {bindable} from "aurelia-templating";
import {ContextResourceClass} from "resources/context/context-resource-class";
import {booleanAttribute} from "../../common/components/boolean-attribute";
import {safeJsonParse} from "../../common/utils/object-utils";
import {getQueryParameters} from "../../common/utils/url-utils";
import {ResourceSort, SortDirection} from "../../resources/resource-sort";
import {Metadata} from "../metadata/metadata";
import {ResourceKind} from "./resource-kind";
import {ResourceKindRepository} from "./resource-kind-repository";

@autoinject
export class ResourceKindsList {
  @bindable @booleanAttribute hideAddButton = false;
  @bindable resourceClass: string;
  @bindable sortable = true;
  @bindable metadata: Metadata;
  resourceKinds: ResourceKind[];
  addFormOpened = false;
  progressBar: boolean;
  sortBy: ResourceSort[];
  private sortButtonToggledSubscription: Subscription;

  constructor(private resourceKindRepository: ResourceKindRepository,
              private contextResourceClass: ContextResourceClass,
              private router: Router,
              private eventAggregator: EventAggregator,
              private i18n: I18N) {
  }

  bind() {
    if (this.metadata) {
      this.sortButtonToggledSubscription = this.eventAggregator.subscribe('sortButtonToggled',
        (parameters: any) => {
          this.activate(parameters);
        });
      this.activate(this.router.currentInstruction.queryParams);
    }
  }

  activate(parameters: any) {
    this.resourceClass = parameters.resourceClass || (this.metadata && this.metadata.resourceClass) || this.resourceClass;
    this.contextResourceClass.setCurrent(this.resourceClass);
    this.sortBy = safeJsonParse(parameters['sortBy']);
    const language = this.i18n.getLocale().toUpperCase();
    this.sortBy = this.sortBy ? this.sortBy : [new ResourceSort('id', SortDirection.DESC, language)];
    if (this.resourceKinds && !this.metadata) {
      this.resourceKinds = [];
    }
    this.progressBar = true;
    this.fetchResourceKinds();
    this.updateURL(true);
  }

  unbind() {
    if (this.sortButtonToggledSubscription) {
      this.sortButtonToggledSubscription.dispose();
    }
  }

  fetchResourceKinds() {
    let query = this.resourceKindRepository.getListQuery()
      .filterByResourceClasses(this.resourceClass)
      .sortByMetadataIds(this.sortBy);
    if (this.metadata) {
      query.filterByMetadataId(this.metadata.id);
    }
    query.get()
      .then(resourceKinds => {
        this.progressBar = false;
        this.resourceKinds = resourceKinds;
        this.addFormOpened = this.resourceKinds.length == 0;
      });
  }

  addNewResourceKind(resourceKind: ResourceKind): Promise<ResourceKind> {
    resourceKind.resourceClass = this.resourceClass;
    return this.resourceKindRepository.post(resourceKind).then(resourceKind => {
      this.addFormOpened = false;
      this.resourceKinds.push(resourceKind);
      return resourceKind;
    });
  }

  toggleEditForm() {
    this.addFormOpened = !this.addFormOpened;
  }

  updateURL(replaceEntryInBrowserHistory?: boolean) {
    let route: string;
    const queryParameters = getQueryParameters();
    const parameters = {};
    parameters['tab'] = queryParameters['tab'];
    if (this.metadata) {
      route = 'metadata/details';
      parameters['id'] = this.metadata.id;
    } else {
      route = 'resource-kinds';
      parameters['resourceClass'] = this.resourceClass;
    }
    parameters['sortBy'] = JSON.stringify(this.sortBy);
    this.router.navigateToRoute(route, parameters, {trigger: false, replace: replaceEntryInBrowserHistory});
  }

  detached() {
    this.metadata = undefined;
  }
}
