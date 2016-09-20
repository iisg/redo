import {Router, RouterConfiguration} from "aurelia-router";

export class App {
  router: Router;

  configureRouter(config: RouterConfiguration, router: Router) {
    config.title = 'RePeKa';
    config.options.pushState = true;
    config.options.root = '/admin';
    config.map([
      {route: '', name: 'home', moduleId: 'home', nav: true, title: 'Strona główna'},
      {route: 'about', name: 'about', moduleId: 'about/about', nav: true, title: 'O nas'},
    ]);
    this.router = router;
  }
}
