import {bindingMode} from "aurelia-binding";

interface BindableConfig {
  // based on http://aurelia.io/hub.html#/doc/article/aurelia/framework/latest/cheat-sheet/8
  name?: string;
  attribute?: string;
  changeHandler?: string;
  defaultBindingMode?: bindingMode;
  defaultValue?: any;
}

class BindableArgument implements BindableConfig {
  constructor(public defaultBindingMode: bindingMode) {
  }

  and(options: BindableConfig): BindableArgument {
    let instance = new BindableArgument(this.defaultBindingMode);
    $.extend(instance, options);
    return instance;
  }
}

export const oneTime: BindableArgument = new BindableArgument(bindingMode.oneTime);
export const twoWay: BindableArgument = new BindableArgument(bindingMode.twoWay);
