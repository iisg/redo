import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";

export class WysiwygEditor {
    @bindable(twoWay) value;
    @bindable placeholder: string;
    @bindable disabled: boolean;
    editor: HTMLInputElement;
    private joditEditor: any;

    contentChanged(newValue: string) {
        if (this.joditEditor && this.joditEditor.value != newValue) {
            this.joditEditor.value = newValue;
        }
    }

    attached() {
        const Jodit = require('jodit'); // tslint:disable-line
        const configuration = {
            "buttonsXS": "source, |, bold, image, |, brush, paragraph, , align, |, undo, redo, |, dots",
            "width": "100%",
            "placeholder": this.placeholder,
            "uploader": {
                insertImageAsBase64URI: true
            }
        };
        if (this.disabled) {
            configuration["toolbarAdaptive"] = false;
            configuration["buttons"] = "source, |, fullsize, selectall, print, about";
            configuration["readonly"] = true;
        }
        this.joditEditor = new Jodit(this.editor, configuration);
        this.joditEditor.value = this.value;
        this.joditEditor.events.on('change', () => {
            this.value = this.joditEditor.value;
        });
    }
}
