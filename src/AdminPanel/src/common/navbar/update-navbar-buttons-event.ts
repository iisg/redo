import {NavModel} from "aurelia-router";
export class UpdateNavbarButtonsEvent {
  readonly items: NavModel[];

  constructor(router: {navigation: NavModel[]}) {
    this.items = router.navigation;
  }
}
