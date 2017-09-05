import {booleanAttribute} from "./boolean-attribute";
import {bindable} from "aurelia-templating";

describe('boolean-attribute', () => {
  it("doesn't interfere with actual booleans", () => {
    const obj = new SimpleTest();
    obj.attr = true;
    obj['attrChanged'](true);
    expect(obj.attr).toBe(true);
    obj.attr = false;
    obj['attrChanged'](false);
    expect(obj.attr).toBe(false);
  });

  it('converts boolean-ish values', () => {
    const obj = new SimpleTest();
    obj.attr = 'true';
    obj['attrChanged']('true');
    expect(obj.attr).toBe(true);
    obj.attr = 1;
    obj['attrChanged'](1);
    expect(obj.attr).toBe(true);
    obj.attr = 0;
    obj['attrChanged'](0);
    expect(obj.attr).toBe(false);
  });

  it('treats empty string as truthy', () => {
    const obj = new SimpleTest();
    obj.attr = '';
    obj['attrChanged']('');
    expect(obj.attr).toBe(true);
  });

  it('calls original change handler with normalized values', () => {
    const obj = new HandlerTest();
    obj.attr = 0;
    obj.attrChanged(0, 1);
    expect(obj.newValue).toBe(false);
    expect(obj.oldValue).toBe(true);
    expect(obj.calls).toBe(1);
  });

  it("doesn't reset flags when change handler is called with no arguments", () => {
    const obj = new ArgumentlessHandlerTest();
    obj.attr = true;
    obj.doFoo();
    expect(obj.attr).toBe(true);
    obj['attrChanged'](undefined);
    expect(obj.attr).toBe(true);
    obj.attr = undefined;
    obj['attrChanged'](undefined);
    expect(obj.attr).toBe(false);
  });

  it("doesn't change flag's value when not called by framework", () => {
    const obj = new HandlerTest();
    obj.attr = false;
    obj.attrChanged(1, 0);
    expect(obj.attr).toBe(false);
    obj.attr = 1;
    obj.attrChanged(1, 0);
    expect(obj.attr).toBe(true);
  });
});

class SimpleTest {
  @bindable @booleanAttribute attr;
}

class HandlerTest {
  @bindable @booleanAttribute attr;
  newValue;
  oldValue;
  calls = 0;

  attrChanged(newValue, oldValue) {
    this.newValue = newValue;
    this.oldValue = oldValue;
    this.calls++;
  }
}

class ArgumentlessHandlerTest {
  @bindable @booleanAttribute attr;

  attrChanged() {
    // some handler code here...
  }

  doFoo() {
    // we want to trigger attrChanged() handler after doing Foo and we don't pass any arguments
    this.attrChanged();
  }
}
