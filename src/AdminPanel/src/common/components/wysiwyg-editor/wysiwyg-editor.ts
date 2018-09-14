import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";

export class WysiwygEditor {
    @bindable(twoWay) content;
    @bindable placeholder: string;
    editor: HTMLInputElement;
    private joditEditor: any;

    contentChanged(newValue: string) {
        if (this.joditEditor && this.joditEditor.value != newValue) {
            this.joditEditor.value = newValue;
        }
    }

    attached() {
        const Jodit = require('jodit'); // tslint:disable-line
        this.joditEditor = new Jodit(this.editor, {
            placeholder: this.placeholder
        });
        this.joditEditor.value = this.content;
        this.joditEditor.events.on('change', () => {
            this.content = this.joditEditor.value;
        });
    }
}
