import {MetricsCollector} from "./metrics-collector";

describe(MetricsCollector.name, () => {
  afterEach(MetricsCollector.flush);

  it("tracks changes of the queue state", () => {
    expect(MetricsCollector.hasStatsInQueue()).toBeFalsy();
    MetricsCollector.increment('sth');
    expect(MetricsCollector.hasStatsInQueue()).toBeTruthy();
  });

  it("tracks the counter", () => {
    MetricsCollector.counter("some.counter", 2);
    expect(MetricsCollector.flush()).toEqual([{type: 'c', name: 'some.counter', value: 2}]);
  });

  it("increments counter", () => {
    MetricsCollector.increment("some.counter");
    expect(MetricsCollector.flush()).toEqual([{type: 'c', name: 'some.counter', value: 1}]);
  });

  it("increments counter two times", () => {
    MetricsCollector.increment("some.counter");
    MetricsCollector.increment("some.counter");
    expect(MetricsCollector.flush()).toEqual([{type: 'c', name: 'some.counter', value: 1}, {type: 'c', name: 'some.counter', value: 1}]);
  });

  it("tracks time", () => {
    MetricsCollector.timeStart("timer");
    expect(MetricsCollector.hasStatsInQueue()).toBeFalsy();
    MetricsCollector.timeEnd("timer");
    expect(MetricsCollector.hasStatsInQueue()).toBeTruthy();
  });

  it("fails on unknown timer", () => {
    expect(() => MetricsCollector.timeEnd("timer")).toThrow();
  });
});
