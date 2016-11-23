import {RouterConfiguration, Router, RoutableComponentActivate, ConfiguresRouter} from "aurelia-router";
import {EventAggregator} from "aurelia-event-aggregator";
import {autoinject} from "aurelia-dependency-injection";
import {UpdateNavbarButtonsEvent} from "../common/navbar/update-navbar-buttons-event";

@autoinject()
export class ResourcesConfig implements RoutableComponentActivate, ConfiguresRouter {
  private readonly eventAggregator: EventAggregator;
  private router: Router;

  constructor(eventAggregator: EventAggregator) {
    this.eventAggregator = eventAggregator;
  }

  configureRouter(config: RouterConfiguration, router: Router): void {
    this.router = router;
    config.map([
      {
        route: ['', 'metadata'],
        name: 'resources-config/metadata',
        moduleId: './metadata/metadata-list',
        nav: true,
        title: 'Metadane'
      },
      {
        route: 'resource-kind',
        name: 'resources-config/resource-kind',
        moduleId: './resource-kind/resource-kind-list',
        nav: true,
        title: 'Rodzaje zasobów'
      },
      {
        route: 'language-list',
        name: 'resources-config/language-list',
        moduleId: './language-config/language-list',
        nav: true,
        title: 'Konfiguracja języków'
      }
    ]);
  }

  activate(): void {
    this.eventAggregator.publish(new UpdateNavbarButtonsEvent(this.router));
  }
}
