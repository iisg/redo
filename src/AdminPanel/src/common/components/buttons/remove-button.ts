import {useView, customElement} from "aurelia-templating";
import {ToggleButton} from "./toggle-button";

@useView('./toggle-button.html')
@customElement('remove-button')
export class RemoveButton extends ToggleButton {
    primaryIconName = 'remove-4';
    primaryLabel = 'Remove';

    bind() {
        super.bind();
    }
}
