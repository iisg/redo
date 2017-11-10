import {cachedResponse, clearCachedResponse, getCachedArgumentsHash} from "./cached-response";

describe("cached response decorator", () => {
  class MyClass {
    @cachedResponse(5)
    getNumber(a: string = undefined) {
      return Math.random();
    }
  }

  let a: MyClass;

  beforeEach(() => {
    a = new MyClass;
  });

  it("caches the value", () => {
    expect(a.getNumber()).toEqual(a.getNumber());
  });

  it("caches the value with argument", () => {
    expect(a.getNumber('a')).toEqual(a.getNumber('a'));
    expect(a.getNumber('b')).toEqual(a.getNumber('b'));
    expect(a.getNumber('a')).not.toEqual(a.getNumber('b'));
  });

  it("allows to manually clear the value", () => {
    let first = a.getNumber();
    clearCachedResponse(a.getNumber);
    expect(a.getNumber).not.toEqual(first);
  });

  it("allows to manually clear the value with argument", () => {
    let aValue = a.getNumber('a');
    let bValue = a.getNumber('b');
    clearCachedResponse(a.getNumber, getCachedArgumentsHash(['a']));
    expect(a.getNumber('a')).not.toEqual(aValue);
    expect(a.getNumber('b')).toEqual(bValue);
  });

  it("clears the value automatically after timeout", (done) => {
    let first = a.getNumber();
    setTimeout(() => {
      expect(a.getNumber()).not.toEqual(first);
      done();
    }, 6);
  });
});
