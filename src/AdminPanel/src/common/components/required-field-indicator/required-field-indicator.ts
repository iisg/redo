import {I18N} from "aurelia-i18n";
import {bindable, inlineView} from "aurelia-templating";

@inlineView('<template bootstrap-tooltip="${translatedTooltipText}">*</template>')
export class RequiredFieldIndicator {
    @bindable translatedTooltipText = this.i18n.tr('Value is required');

    constructor(private i18n: I18N) {
    }
}
