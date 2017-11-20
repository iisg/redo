import Promise from "bluebird";
import {cachedResponse, clearCachedResponse, getCachedArgumentsHash, forSeconds, untilPromiseCompleted} from "./cached-response";

describe(cachedResponse.name, () => {
  class TestClass {
    @cachedResponse()
    getNumber(whatever?: any): number {
      whatever;
      return Math.random();
    }

    @cachedResponse(forSeconds(0.01))
    getNumberTimeout(): number {
      return Math.random();
    }

    @cachedResponse(untilPromiseCompleted)
    getNumberPromise(): Promise<number> {
      return Promise.resolve(Math.random());
    }

    @cachedResponse(untilPromiseCompleted)
    getRejectedPromise(): Promise<number> {
      return Promise.reject(new Error());
    }

    @cachedResponse(untilPromiseCompleted)
    getDelayedNumberPromise(): Promise<number> {
      return new Promise(resolve => setTimeout(resolve, 0.01));
    }
  }

  let a: TestClass;

  beforeEach(() => {
    a = new TestClass();
    clearCachedResponse(a.getNumber);
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

  it("clears after timeout", done => {
    const first = a.getNumberTimeout();
    expect(a.getNumberTimeout()).toEqual(first);
    setTimeout(() => {
      expect(a.getNumberTimeout()).not.toEqual(first);
      done();
    }, 100);
  });

  it("clears when promise is resolved", done => {
    const first = a.getNumberPromise();
    expect(a.getNumberPromise()).toBe(first);
    first.then(() => {
      expect(a.getNumberPromise()).not.toBe(first);
    }).then(done);
  });

  it("clears when promise is rejected", done => {
    const first = a.getRejectedPromise();
    expect(a.getRejectedPromise()).toBe(first);
    first.catch(() => {
      expect(a.getRejectedPromise()).not.toBe(first);
    }).then(done);
  });

  it("clears when non-immediate promise is resolved", done => {
    const first = a.getDelayedNumberPromise();
    expect(a.getDelayedNumberPromise()).toBe(first);
    first.then(() => {
      expect(a.getDelayedNumberPromise()).not.toBe(first);
    }).then(done);
  });
});
