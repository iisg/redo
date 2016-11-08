import {Interceptor, RequestMessage} from "aurelia-http-client";
import {MetricsCollector} from "./metrics-collector";

export class MetricsSenderInterceptor implements Interceptor {
  request(request: RequestMessage): RequestMessage {
    if (MetricsCollector.hasStatsInQueue() && btoa) {
      let stats = MetricsCollector.flush();
      let headerValue = btoa(JSON.stringify(stats));
      request.headers.add('X-Metrics', headerValue);
    }
    return request;
  }
}
