import {bindable} from "aurelia-templating";
import {booleanAttribute} from "../boolean-attribute";

export class IconButton {
    @bindable iconName: string;
    @bindable tooltipTextWhenEnabled: string;
    @bindable tooltipTextWhenDisabled: string;
    @bindable onClick: () => void;
    @bindable @booleanAttribute disabled: boolean;
}
