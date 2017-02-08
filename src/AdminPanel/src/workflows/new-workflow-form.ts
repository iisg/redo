import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {autoinject} from "aurelia-dependency-injection";
import {Workflow} from "./workflow";
import {BootstrapValidationRenderer} from "common/validation/bootstrap-validation-renderer";
import {WorkflowRepository} from "./workflow-repository";
import {Router} from "aurelia-router";

@autoinject
export class NewWorkflowForm {
  workflow: Workflow = new Workflow;
  submitting: boolean = false;

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory, private workflowRepository: WorkflowRepository,
              private router: Router) {
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer());
  }

  validateAndSubmit() {
    this.submitting = true;
    this.controller.validate().then(result => {
      if (result.valid) {
        this.workflowRepository.post(this.workflow).then(workflow => {
          this.workflow = new Workflow;
          this.router.navigateToRoute('workflow/details', {id: workflow.id});
        });
      }
    }).finally(() => this.submitting = false);
  }
}
