import {useView, customElement} from "aurelia-templating";
import {DefaultButton} from "./default-button";

@useView('./default-button.html')
@customElement('remove-button')
export class RemoveButton extends DefaultButton {
    primaryIcon = 'remove-4';
    primaryLabel = 'Remove';

    bind() {
        super.bind();
    }
}
