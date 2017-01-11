import {cachedResponse, clearCachedResponse} from "./cached-response";

describe("cached response decorator", () => {
  class MyClass {
    @cachedResponse(5)
    getNumber() {
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

  it("allows to manually clear the value", () => {
    let first = a.getNumber();
    clearCachedResponse(a.getNumber);
    expect(a.getNumber).not.toEqual(first);
  });

  it("clears the value automatically after timeout", (done) => {
    let first = a.getNumber();
    setTimeout(() => {
      expect(a.getNumber).not.toEqual(first);
      done();
    }, 6);
  });
});