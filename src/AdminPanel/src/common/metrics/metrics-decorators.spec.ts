import {MetricsCollector} from "./metrics-collector";
import {metricIncrement, metricTime} from "./metrics-decorators";
import Spy = jasmine.Spy;

describe("metrics decorators", () => {
  afterEach(MetricsCollector.flush);

  describe(metricIncrement.name, () => {
    class MetricIncrementTester {
      @metricIncrement("some.name")
      trackedMethod() {
      }

      untrackedMethod() {
      }
    }

    beforeEach(() => {
      spyOn(MetricsCollector, 'increment');
    });

    it("increments the metric", () => {
      new MetricIncrementTester().trackedMethod();
      expect(MetricsCollector.increment).toHaveBeenCalledWith('some.name');
    });

    it("does not increment the metric on unannotated method", () => {
      new MetricIncrementTester().untrackedMethod();
      expect(MetricsCollector.increment).not.toHaveBeenCalled();
    });
  });

  describe(metricTime.name, () => {
    class MetricTimeTester {
      resolve;

      promise = new Promise((resolve) => {
        this.resolve = resolve;
      });

      @metricTime("some.name")
      trackedMethod() {
      }

      @metricTime("some.name.promised")
      trackedPromiseMethod() {
        return this.promise;
      }
    }

    beforeEach(() => {
      spyOn(MetricsCollector, 'time');
    });

    it("measures the time of the method", () => {
      new MetricTimeTester().trackedMethod();
      expect(MetricsCollector.time).toHaveBeenCalled();
      let spy = MetricsCollector.time as Spy;
      let args = spy.calls.mostRecent().args;
      expect(args[0]).toEqual('some.name');
      expect(args[1]).toBeLessThanOrEqual(100);
    });

    it("measures the time of the method that returns promise", (done) => {
      let tester = new MetricTimeTester();
      let e = false;
      tester.trackedPromiseMethod().then(() => e = true);
      expect(MetricsCollector.time).not.toHaveBeenCalled();
      setTimeout(tester.resolve, 5);
      tester.promise.then(() => {
        expect(MetricsCollector.time).toHaveBeenCalled();
        let spy = MetricsCollector.time as Spy;
        let args = spy.calls.mostRecent().args;
        expect(args[0]).toEqual('some.name.promised');
        expect(args[1]).toBeGreaterThanOrEqual(5);
        done();
      });
    });
  });
});
