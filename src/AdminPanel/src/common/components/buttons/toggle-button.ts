import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {bindable} from "aurelia-templating";

@autoinject
export class ToggleButton {
    @bindable type = "button";
    @bindable primaryIconName: string;
    @bindable primaryLabel: string;
    @bindable secondaryIconName: string;
    @bindable secondaryLabel: string;
    @bindable entityName: string;
    @bindable showTooltipsInsteadOfLabels: boolean;
    @bindable toggled: boolean;
    @bindable throbberDisplayed: boolean;
    @bindable disabled: boolean;
    @bindable onClick: () => void;
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
