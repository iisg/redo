// https://github.com/aurelia/binding/issues/533

interface ToViewValueConverter {
  toView(modelValue: any, ...params): any;
}

interface FromViewValueConverter {
  fromView(viewValue: any): any;
}
