import {PipelineStep, NavigationInstruction, Next} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {StaticPermissionsChecker} from "../authorization/static-permissions-checker";

@autoinject
export class RouteAccessChecker implements PipelineStep {
  constructor(private staticPermissionsChecker: StaticPermissionsChecker) {
  }

  run(instruction: NavigationInstruction, next: Next): Promise<any> {
    for (let inst of instruction.getAllInstructions()) {
      if (!this.staticPermissionsChecker.allAllowed(inst.config.settings.staticPermissions || [])) {
        return next.cancel(); // TODO redirect to 403 error page, REPEKA-103
      }
    }
    return next();
  }
}