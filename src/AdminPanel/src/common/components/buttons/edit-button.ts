import {useView, customElement} from "aurelia-templating";
import {ToggleButton} from "./toggle-button";

@useView('./toggle-button.html')
@customElement('edit-button')
export class EditButton extends ToggleButton {
    primaryIconName = 'edit-2';
    primaryLabel = 'Edit';
    secondaryIconName = 'remove-3';
    secondaryLabel = 'Cancel';

    bind() {
        super.bind();
    }
}
