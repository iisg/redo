import {NavigationInstruction, Next, PipelineStep, Redirect} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {HasRoleValueConverter} from "../authorization/has-role-value-converter";

@autoinject
export class RouteAccessChecker implements PipelineStep {
  constructor(private hasRole: HasRoleValueConverter) {
  }

  run(instruction: NavigationInstruction, next: Next): Promise<any> {
    for (let inst of instruction.getAllInstructions()) {
      const requiredRole = inst.config.settings && inst.config.settings.requiredRole;
      if (requiredRole && !this.hasRole.toView(requiredRole, inst.params.resourceClass)) {
        return next.cancel(new Redirect('not-allowed'));
      }
    }
    return next();
  }
}
