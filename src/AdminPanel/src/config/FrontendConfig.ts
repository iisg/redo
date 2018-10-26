export class FrontendConfig {
  public static get(key: string) {
    return window['FRONTEND_CONFIG'][key];
  }
}
