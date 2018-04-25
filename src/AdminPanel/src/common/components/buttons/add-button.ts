import {useView, customElement} from "aurelia-templating";
import {ToggleButton} from "./toggle-button";

@useView('./toggle-button.html')
@customElement('add-button')
export class AddButton extends ToggleButton {
    primaryIconName = 'add-resource-3';
    primaryLabel = 'Add';
    secondaryIconName = 'remove-3';
    secondaryLabel = 'Cancel';
    onClick = () => {
        this.toggled = !this.toggled;
    }

    bind() {
        super.bind();
    }
}
