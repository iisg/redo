declare module "aurelia-dialog" {
  export class DialogController {
    ok: any;

    cancel(): void;

    settings: any;
  }

  export class DialogService {
    open(settings: any): any;
  }

  export interface DialogComponentActivate<T> {
    activate(model?: T): void | Promise<void> | PromiseLike<void>;
  }

  export class DialogConfiguration {
    private fwConfig;
    private renderer;
    private cssText;
    private resources;
    /**
     * The global configuration settings.
     */
    settings: any;

    constructor(frameworkConfiguration: any, applySetter: (apply: () => void) => void);

    private _apply();

    /**
     * Selects the Aurelia conventional defaults for the dialog plugin.
     * @return This instance.
     */
    useDefaults(): this;

    /**
     * Exports the standard set of dialog behaviors to Aurelia's global resources.
     * @return This instance.
     */
    useStandardResources(): this;

    /**
     * Exports the chosen dialog element or view to Aurelia's global resources.
     * @param resourceName The name of the dialog resource to export.
     * @return This instance.
     */
    useResource(resourceName: any): this;

    /**
     * Configures the plugin to use a specific dialog renderer.
     * @param renderer A type that implements the Renderer interface.
     * @param settings Global settings for the renderer.
     * @return This instance.
     */
    useRenderer(renderer: any, settings?: any): this;

    /**
     * Configures the plugin to use specific css. You can pass an empty string to clear any set css.
     * @param cssText The css to use in place of the default styles.
     * @return This instance.
     */
    useCSS(cssText: string): this;
  }
}
