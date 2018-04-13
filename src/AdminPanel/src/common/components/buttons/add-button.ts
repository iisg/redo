import {useView, customElement} from "aurelia-templating";
import {DefaultButton} from "./default-button";

@useView('./default-button.html')
@customElement('add-button')
export class AddButton extends DefaultButton {
    primaryIcon = 'add-resource-3';
    primaryLabel = 'Add';
    secondaryIcon = 'remove-3';
    secondaryLabel = 'Cancel';
    onClick = () => {
        this.toggled = !this.toggled;
    }

    bind() {
        super.bind();
    }
}
