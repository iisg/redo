import {Router, RouterConfiguration, ConfiguresRouter} from "aurelia-router";
import {ComponentAttached} from "aurelia-templating";

export class App implements ConfiguresRouter, ComponentAttached {
  router: Router;

  configureRouter(config: RouterConfiguration, router: Router) {
    config.title = 'RePeKa';
    config.options.pushState = true;
    config.options.root = '/admin';
    config.map([
      {route: '', name: 'home', moduleId: 'home', nav: true, title: 'Przegląd', settings: {icon: 'dashboard'}},
      {
        route: 'import',
        name: 'import',
        moduleId: 'data-import/data-import',
        nav: true,
        title: 'Import danych',
        settings: {icon: 'download'}
      },
      {route: 'data', name: 'home', moduleId: 'home', nav: true, title: 'Konfiguracja zasobów', settings: {icon: 'database'}},
      {route: 'users', name: 'home', moduleId: 'home', nav: true, title: 'Użytkownicy', settings: {icon: 'group'}},
      {route: 'about', name: 'about', moduleId: 'about/about', nav: true, title: 'System', settings: {icon: 'info-circle'}},
    ]);
    this.router = router;
  }

  attached() {
    $.material.init();
  }
}
