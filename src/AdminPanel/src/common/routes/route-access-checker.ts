import {PipelineStep, NavigationInstruction, Next} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {StaticPermissionsChecker} from "../authorization/static-permissions-checker";
import {Redirect} from "aurelia-router";

@autoinject
export class RouteAccessChecker implements PipelineStep {
  constructor(private staticPermissionsChecker: StaticPermissionsChecker) {
  }

  run(instruction: NavigationInstruction, next: Next): Promise<any> {
    for (let inst of instruction.getAllInstructions()) {
      if (!this.staticPermissionsChecker.allAllowed(inst.config.settings && inst.config.settings.staticPermissions || [])) {
        return next.cancel(new Redirect('not-allowed'));
      }
    }
    return next();
  }
}
