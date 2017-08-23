import {booleanAttribute} from "./boolean-attribute";
import {bindable} from "aurelia-templating";

describe('boolean-attribute', () => {
  it("doesn't interfere with actual booleans", () => {
    const obj = new SimpleTest();
    obj['attrChanged'](true);
    expect(obj.attr).toBe(true);
    obj['attrChanged'](false);
    expect(obj.attr).toBe(false);
  });

  it('converts boolean-ish values', () => {
    const obj = new SimpleTest();
    obj['attrChanged']('true');
    expect(obj.attr).toBe(true);
    obj['attrChanged'](1);
    expect(obj.attr).toBe(true);
    obj['attrChanged'](0);
    expect(obj.attr).toBe(false);
  });

  it('treats empty string as truthy', () => {
    const obj = new SimpleTest();
    obj['attrChanged']('');
    expect(obj.attr).toBe(true);
  });

  it('calls original change handler with normalized values', () => {
    const obj = new HandlerTest();
    obj['attrChanged'](0, 1);
    expect(obj.newValue).toBe(false);
    expect(obj.oldValue).toBe(true);
    expect(obj.calls).toBe(1);
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
