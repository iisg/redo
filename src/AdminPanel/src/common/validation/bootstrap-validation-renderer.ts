import {RenderInstruction, ValidateResult, ValidationRenderer} from "aurelia-validation";

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

    const validationMessageContainer = this.findValidationMessageContainer(element);
    if (!validationMessageContainer) {
      return;
    }

    // Adds the 'has-error' class to the validation message container.
    validationMessageContainer.classList.add('has-error');

    // Adds validation message.
    const message = document.createElement('span');
    message.className = 'help-block validation-message';
    message.textContent = result.message;
    message.id = `validation-message-${result.id}`;
    const restoreFromOriginalButton = $(validationMessageContainer).find('restore-from-original-button');
    if (restoreFromOriginalButton.length) {
      $(message).insertBefore(restoreFromOriginalButton);
    } else {
      validationMessageContainer.appendChild(message);
    }
  }

  private remove(element: Element, result: ValidateResult) {
    if (result.valid) {
      return;
    }

    const validationMessageContainer = this.findValidationMessageContainer(element);
    if (!validationMessageContainer) {
      return;
    }

    // Removes validation message.
    const message = validationMessageContainer.querySelector(`#validation-message-${result.id}`);
    if (message) {
      validationMessageContainer.removeChild(message);

      // Removes the 'has-error' class from the validation message container.
      if (validationMessageContainer.querySelectorAll('.help-block.validation-message').length === 0) {
        validationMessageContainer.classList.remove('has-error');
      }
    }
  }

  private findValidationMessageContainer(element: Element): Element {
    const errorContainer = $(element).closest('.validation-message-container, .simple-form, .form-group, \
      metadata-value-input, resource-metadata-values-form, resource-form-generated')[0]
      || $(element).prev()[0];
    return errorContainer.nodeName.toLowerCase() == 'resource-metadata-values-form'
      ? $(errorContainer).find('.validation-message-container')[0] || errorContainer
      : errorContainer;
  }
}
