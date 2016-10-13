import {RouterConfiguration, Router, RoutableComponentActivate, ConfiguresRouter} from "aurelia-router";
import {EventAggregator} from "aurelia-event-aggregator";
import {autoinject} from "aurelia-dependency-injection";
import {UpdateNavbarButtonsEvent} from "../common/navbar/update-navbar-buttons-event";

@autoinject()
export class DataImport implements RoutableComponentActivate, ConfiguresRouter {
  private readonly eventAggregator: EventAggregator;
  private router: Router;

  constructor(eventAggregator: EventAggregator) {
    this.eventAggregator = eventAggregator;
  }

  configureRouter(config: RouterConfiguration, router: Router): void {
    this.router = router;
    config.map([
      {route: ['', 'koha'], name: 'import/koha', moduleId: './data-import-koha', nav: true, title: 'Koha'},
      {route: 'excel', name: 'import/excel', moduleId: './data-import-excel', nav: true, title: 'Excel'},
    ]);
  }

  activate(): void {
    this.eventAggregator.publish(new UpdateNavbarButtonsEvent(this.router));
  }
}
