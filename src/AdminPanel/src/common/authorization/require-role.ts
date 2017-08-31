import {autoinject} from "aurelia-dependency-injection";
import {UserRoleChecker} from "./user-role-checker";
import {BoundViewFactory, ViewSlot, templateController, customAttribute, View, ComponentBind, ComponentUnbind} from "aurelia-templating";
import {bindingMode} from "aurelia-binding";

abstract class Displayable {
  private view: View;
  private bindingContext;
  private overrideContext;

  constructor(private viewFactory: BoundViewFactory, protected viewSlot: ViewSlot) {
  }

  bind(bindingContext, overrideContext) {
    this.bindingContext = bindingContext;
    this.overrideContext = overrideContext;
  }

  unbind() {
    if (this.view != undefined) {
      this.view.unbind();
      this.viewSlot.remove(this.view);
    }
  }

  setVisibility(visible: boolean): void {
    if (visible) {
      this.view = this.viewFactory.create();
      this.view.bind(this.bindingContext, this.overrideContext);
      this.viewSlot.add(this.view);
    } else {
      this.viewSlot.removeAll();
    }
  }
}

/*
 * Note that Aurelia doesn't allow multiple template controllers on a single element.
 * It means that require-role WON'T WORK WITH IF.BIND, REPEAT.FOR and other attributes that manipulate DOM.
 * If you need to use both, wrap element in a <template> and move one template controller attribute to <template>.
 */
@customAttribute('require-role', bindingMode.oneTime)
@templateController
@autoinject
export class RequireRole extends Displayable implements ComponentBind, ComponentUnbind {
  private requiredRoles: Array<string> = [];

  opposite: RequiredRoleMissing;

  constructor(private userRoleChecker: UserRoleChecker,
              viewFactory: BoundViewFactory,
              viewSlot: ViewSlot) {
    super(viewFactory, viewSlot);
  }

  bind(bindingContext, overrideContext) {
    super.bind(bindingContext, overrideContext);
    this.valueChanged(this['value']);
  }

  valueChanged(roles: string|string[]) {
    if (roles instanceof Array) {
      this.requiredRoles = roles;
    } else if (typeof roles == 'string') {
      this.requiredRoles = (roles as string).split(',');
    } else {
      this.requiredRoles = [];
    }
    this.requiredRoles = this.requiredRoles.map(p => p.trim()).filter(p => !!p);

    const hasRequiredRoles = this.userRoleChecker.hasAll(this.requiredRoles);
    this.setVisibility(hasRequiredRoles);
    if (this.opposite != undefined) {
      this.opposite.setVisibility(!hasRequiredRoles);
    }
  }
}

// Based on if.js in aurelia-templating-resources
// Doesn't run any logic by itself, just registers in previous require-role and lets it do the thing.
@customAttribute('required-role-missing')
@templateController
@autoinject
export class RequiredRoleMissing extends Displayable {
  constructor(viewFactory: BoundViewFactory, viewSlot: ViewSlot) {
    super(viewFactory, viewSlot);
    this.registerAsComplementary();
  }

  private registerAsComplementary() {
    const node: Node = this.viewSlot['anchor'];
    let previous = node.previousSibling;
    while (previous && !previous['au']) {
      previous = previous.previousSibling;
    }
    if (!previous || !previous['au']['require-role']) {
      throw new Error("Can't find matching RequireRole for RequiredRoleMissing custom attribute.");
    }
    const viewModel: RequireRole = previous['au']['require-role']['viewModel'];
    viewModel.opposite = this;
  }
}
