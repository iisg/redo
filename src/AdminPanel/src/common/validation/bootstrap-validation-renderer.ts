import {ValidationRenderer, RenderInstruction, ValidateResult} from "aurelia-validation";

/**
 * Renders form errors in bootstrap form with form-groups.
 * @see http://aurelia.io/hub.html#/doc/article/aurelia/validation/latest/validation-basics/8
 * @see https://gist.github.com/jdanyow/ea843c24956cfffff48bb21776291f6a
 */
export class BootstrapValidationRenderer implements ValidationRenderer {
  render(instruction: RenderInstruction) {
    for (let {result, elements} of instruction.unrender) {
      for (let element of elements) {
        this.remove(element, result);
      }
    }

    for (let {result, elements} of instruction.render) {
      for (let element of elements) {
        this.add(element, result);
      }
    }
  }

  private add(element: Element, result: ValidateResult) {
    if (result.valid) {
      return;
    }

    const formGroup = $(element).closest('.form-group')[0];
    if (!formGroup) {
      return;
    }

    // add the has-error class to the enclosing form-group div
    formGroup.classList.add('has-error');

    // add help-block
    const message = document.createElement('span');
    message.className = 'help-block validation-message';
    message.textContent = result.message;
    message.id = `validation-message-${result.id}`;
    formGroup.appendChild(message);
  }

  private remove(element: Element, result: ValidateResult) {
    if (result.valid) {
      return;
    }

    const formGroup = $(element).closest('.form-group')[0];
    if (!formGroup) {
      return;
    }

    // remove help-block
    const message = formGroup.querySelector(`#validation-message-${result.id}`);
    if (message) {
      formGroup.removeChild(message);

      // remove the has-error class from the enclosing form-group div
      if (formGroup.querySelectorAll('.help-block.validation-message').length === 0) {
        formGroup.classList.remove('has-error');
      }
    }
  }
}
