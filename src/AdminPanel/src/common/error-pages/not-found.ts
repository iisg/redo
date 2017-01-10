import {metricIncrement} from "../metrics/metrics-decorators";

export class NotFound {
  @metricIncrement("error_404")
  attached() {
  }
}
