declare interface NumberConstructor {
  // built-in definitions specify argument as a number, while the function will accept any:
  // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number/isInteger
  isInteger(value: any): boolean;
}
