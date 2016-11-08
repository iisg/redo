export class MetricsCollector {
  private static readonly MAX_FLUSH_AT_ONCE = 30;

  private static queue: MetricsCollectorEntry[] = [];

  private static timings = {};

  private constructor() {
  }

  static increment(name: string) {
    MetricsCollector.counter(name, 1);
  }

  static counter(name: string, value: number) {
    this.add('c', name, Math.round(value));
  }

  static time(name: string, ms: number) {
    this.add('t', name, Math.round(ms));
  }

  static timeStart(name: string) {
    MetricsCollector.timings[name] = new Date().getTime();
  }

  static timeEnd(name: string) {
    if (!MetricsCollector.timings[name]) {
      throw new Error('Invalid timer referenced: ' + name);
    }
    let end = new Date().getTime();
    let time = end - MetricsCollector.timings[name];
    delete MetricsCollector.timings[name];
    this.time(name, time);
  }

  static hasStatsInQueue() {
    return MetricsCollector.queue.length > 0;
  }

  static flush(): MetricsCollectorEntry[] {
    return MetricsCollector.queue.splice(0, MetricsCollector.MAX_FLUSH_AT_ONCE);
  }

  private static add(type: string, name: string, value: number) {
    MetricsCollector.queue.push({type, name, value});
  }
}

export interface MetricsCollectorEntry {
  type: string;
  name: string;
  value: number;
}
