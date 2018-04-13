import {useView, customElement} from "aurelia-templating";
import {DefaultButton} from "./default-button";

@useView('./default-button.html')
@customElement('edit-button')
export class EditButton extends DefaultButton {
    primaryIcon = 'edit-2';
    primaryLabel = 'Edit';
    secondaryIcon = 'remove-3';
    secondaryLabel = 'Cancel';

    bind() {
        super.bind();
    }
}
