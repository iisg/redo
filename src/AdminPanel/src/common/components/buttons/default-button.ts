import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";
import {booleanAttribute} from "../boolean-attribute";
import {I18N} from "aurelia-i18n";

@autoinject
export class DefaultButton {
    @bindable primaryIcon: string;
    @bindable primaryLabel: string;
    @bindable secondaryIcon: string;
    @bindable secondaryLabel: string;
    @bindable entityName: string;
    @bindable onClick: () => void;
    @bindable @booleanAttribute toggled: boolean = false;
    @bindable @booleanAttribute disabled: boolean;
    displayedPrimaryLabel: string;
    displayedSecondaryLabel: string;

    constructor(protected i18n: I18N) {
    }

    bind() {
        if (!this.displayedPrimaryLabel) {
            let displayedPrimaryLabel = this.i18n.tr(this.primaryLabel);
            if (this.entityName) {
                displayedPrimaryLabel += ' ' + this.i18n.tr('entity_types::' + this.entityName, {context: 'accusative'});
            }
            this.displayedPrimaryLabel = displayedPrimaryLabel;
        }
        if (this.secondaryLabel && !this.displayedSecondaryLabel) {
            this.displayedSecondaryLabel = this.i18n.tr(this.secondaryLabel);
        }
    }
}
