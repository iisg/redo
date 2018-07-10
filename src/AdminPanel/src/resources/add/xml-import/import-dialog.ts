import {DialogController} from "aurelia-dialog";
import {autoinject} from "aurelia-dependency-injection";
import {XmlImportClient} from "./xml-import-client";
import {observable} from "aurelia-binding";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {GlobalExceptionInterceptor} from "common/http-client/global-exception-interceptor";
import {HttpResponseMessage} from "aurelia-http-client";

@autoinject
export class ImportDialog {
  resourceKind: ResourceKind;

  importConfig: XmlImportConfig;
  resourceId: string = '';

  @observable configFile: File = undefined;
  importPending: boolean = false;
  configFileError: boolean = false;
  notFoundError: boolean = false;
  serverError: string;
  importFileIsFocused: boolean = false;

  private readonly MOST_RECENT_CONFIG_KEY = 'mostRecentXmlImportConfig';

  constructor(private dialogController: DialogController,
              private xmlImportClient: XmlImportClient,
              private globalExceptionInterceptor: GlobalExceptionInterceptor) {
    dialogController.settings.lock = false;
    dialogController.settings.keyboard = true;
    const storedJson = localStorage[this.MOST_RECENT_CONFIG_KEY];
    if (storedJson !== undefined) {
      try {
        const storedConfig = JSON.parse(storedJson);
        if (!('fileName' in storedConfig) || !('json' in storedConfig) || Object.keys(storedConfig).length > 2) {
          delete localStorage[this.MOST_RECENT_CONFIG_KEY];
        } else {
          this.importConfig = storedConfig;
        }
      } catch (_) {
        // value is invalid, erase it
        delete localStorage[this.MOST_RECENT_CONFIG_KEY];
      }
    }
  }

  activate(model: ImportDialogModel): void {
    this.resourceKind = model.resourceKind;
  }

  handleEscPressed(event) {
    if (event.keyCode == 27) {
      this.importFileIsFocused = false;
    }
    return true;
  }

  canDeactivate() {
    return !this.importFileIsFocused;
  }

  configFileChanged(): void {
    // Ideally, this should be done in a value converter, but FileReader is asynchronous and value converters are not
    const reader = new FileReader();
    reader.readAsText(this.configFile);
    reader.onload = () => {
      this.configFileError = false;
      this.importConfig = undefined;
      try {
        JSON.parse(reader.result);
      } catch (e) {
        this.configFileError = true;
      }
      this.importConfig = {
        fileName: this.configFile.name,
        json: reader.result,
      };
      localStorage[this.MOST_RECENT_CONFIG_KEY] = JSON.stringify(this.importConfig);
    };
  }

  downloadResource() {
    this.importPending = true;
    this.notFoundError = false;
    this.serverError = undefined;
    this.xmlImportClient.getMetadataValues(this.resourceId, this.importConfig.json, this.resourceKind)
      .then(importResult => {
        this.dialogController.ok(importResult);
      })
      .catch((response: HttpResponseMessage) => {
        if (response.statusCode == 404) {
          this.notFoundError = true;
        } else if (response.statusCode == 400) {
          this.serverError = this.globalExceptionInterceptor.getErrorMessage(response);
        } else {
          this.globalExceptionInterceptor.responseError(response);
        }
      })
      .finally(() => this.importPending = false);
  }
}

export class SingleFileListValueConverter implements FromViewValueConverter {
  fromView(fileList: FileList): File {
    return fileList.length > 0 ? fileList.item(0) : undefined;
  }
}

interface ImportDialogModel {
  resourceKind: ResourceKind;
}

interface XmlImportConfig {
  fileName: string;
  json: string;
}
