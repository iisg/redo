import {bindingMode} from "aurelia-binding";

interface BindableArgument {
  defaultBindingMode: bindingMode;
}

export const oneTime: BindableArgument = {defaultBindingMode: bindingMode.oneTime};
export const twoWay: BindableArgument = {defaultBindingMode: bindingMode.twoWay};
