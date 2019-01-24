import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {HttpClient} from "aurelia-http-client";
import {NavigationInstruction} from "aurelia-router";
import {MetricsCollector} from "./metrics-collector";

@autoinject
export class MetricsEventListener {
  constructor(private eventAggregator: EventAggregator, private httpClient: HttpClient) {
  }

  register() {
    this.listenForRouterEvents();
    this.sendMetricsPeriodically();
  }

  private listenForRouterEvents() {
    this.eventAggregator.subscribe('router:navigation:processing', (event: { instruction: NavigationInstruction }) => {
      let viewName = event.instruction.fragment.substr(1).replace(/\//g, '.').replace(/\.$/, '') || "home";
      MetricsCollector.increment(`view.${viewName}`);
    });
  }

  private sendMetricsPeriodically() {
    setInterval(() => {
      if (MetricsCollector.hasStatsInQueue()) {
        this.httpClient.get('noop'); // sends the metrics via interceptor
      }
    }, 30000);
  }
}
