import {PipelineStep, NavigationInstruction, Next, Redirect} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {UserRoleChecker} from "../authorization/user-role-checker";

@autoinject
export class RouteAccessChecker implements PipelineStep {
  constructor(private userRoleChecker: UserRoleChecker) {
  }

  run(instruction: NavigationInstruction, next: Next): Promise<any> {
    for (let inst of instruction.getAllInstructions()) {
      if (!this.userRoleChecker.hasAll(inst.config.settings && inst.config.settings.requiredRoles || [])) {
        return next.cancel(new Redirect('not-allowed'));
      }
    }
    return next();
  }
}
