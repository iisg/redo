import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";

export class LocalStorageValueCustomAttribute {
    @bindable key: string;
    @bindable(twoWay) value: any;

    bind() {
        try {
            const value = JSON.parse(localStorage.getItem(this.key));
            if (value) {
                this.value = value;
            }
        } catch (exception) {}
    }

    valueChanged() {
        try {
            localStorage.setItem(this.key, JSON.stringify(this.value));
        } catch (exception) {}
    }
}