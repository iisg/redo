import {metricIncrement} from "common/metrics/metrics-decorators";

export class NotAllowed {
  @metricIncrement("error_403")
  attached() {
  }
}
