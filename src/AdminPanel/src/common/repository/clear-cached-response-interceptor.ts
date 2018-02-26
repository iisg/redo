import {Interceptor, RequestMessage} from "aurelia-http-client";
import {inArray} from "../utils/array-utils";
import {cachedResponseRegistry} from "./cached-response";

export class ClearCachedResponseInterceptor implements Interceptor {
  request(request: RequestMessage): RequestMessage {
    if (!inArray(request.method, ['GET', 'HEAD', 'OPTIONS'])) {
      cachedResponseRegistry.clearAll();
    }
    return request;
  }
}
