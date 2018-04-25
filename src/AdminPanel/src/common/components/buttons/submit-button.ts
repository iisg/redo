import {useView, customElement, bindable} from "aurelia-templating";
import {booleanAttribute} from "../boolean-attribute";
import {ToggleButton} from "./toggle-button";

@useView('./toggle-button.html')
@customElement('submit-button')
export class SubmitButton extends ToggleButton {
    @bindable @booleanAttribute editing: boolean;
    @bindable @booleanAttribute submitting: boolean;
    type="submit";
    primaryIconName = 'add';
    primaryLabel = 'Add';
    secondaryIconName = 'accept-2';
    secondaryLabel = 'Apply';

    bind() {
        super.bind();
    }

    get toggled(): boolean {
        return this.editing;
    }

    get throbberDisplayed(): boolean {
        return this.submitting;
    }

    get disabled(): boolean {
        return this.submitting;
    }
}
