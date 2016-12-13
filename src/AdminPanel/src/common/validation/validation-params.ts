export class ValidationParams {
  private params = {};

  public put(ruleName: string, param: string, value: string) {
    if (!(ruleName in this.params)) {
      this.params[ruleName] = {};
    }
    this.params[ruleName][param] = value;
  }

  private getForRule(ruleName: string): {} {
    return this.params[ruleName] || {};
  }

  public parametrize(input: string, ruleName: string) {
    const params = this.getForRule(ruleName);
    for (let param in params) {
      const placeholder = '${' + param + '}';
      // input.replace with string argument replaces only first occurence.
      // input.replace with regex argument is prone to regex escaping issues.
      // This trick is slightly slower, but clean and effective:
      input = input.split(placeholder).join(params[param]);
    }
    return input;
  }
}
