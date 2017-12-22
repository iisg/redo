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
  name?: string;
  attribute?: string;
  changeHandler?: string;
  defaultBindingMode?: bindingMode;
  defaultValue?: any;

  constructor(config: BindableConfig) {
    $.extend(this, config);
  }

  and(options: BindableConfig): BindableArgument {
    let instance = new BindableArgument(this);
    $.extend(instance, options);
    return instance;
  }
}

export const oneTime: BindableArgument = new BindableArgument({defaultBindingMode: bindingMode.oneTime});
export const twoWay: BindableArgument = new BindableArgument({defaultBindingMode: bindingMode.twoWay});

export function changeHandler(methodName: string): BindableArgument {
  return new BindableArgument({changeHandler: methodName});
}
