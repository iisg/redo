import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {LocalStorage} from "common/utils/local-storage";

export class LocalStorageValueCustomAttribute {
    @bindable key: string;
    @bindable(twoWay) value: any;

    bind() {
        const value = LocalStorage.get(this.key);
        if (value != undefined) {
            this.value = value;
        }
    }

    valueChanged() {
        LocalStorage.set(this.key, this.value);
    }
}
