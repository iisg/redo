import {observable} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {DialogController} from "aurelia-dialog";
import {HttpResponseMessage} from "aurelia-http-client";
import {GlobalExceptionInterceptor} from "common/http-client/global-exception-interceptor";
import {LocalStorage} from "common/utils/local-storage";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {XmlImportClient} from "./xml-import-client";

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
    const storedConfiguration = LocalStorage.get(this.MOST_RECENT_CONFIG_KEY);
    if (storedConfiguration !== undefined) {
      if (!('fileName' in storedConfiguration) || !('fileContents' in storedConfiguration) || Object.keys(storedConfiguration).length > 2) {
        LocalStorage.remove(this.MOST_RECENT_CONFIG_KEY);
      } else {
        this.importConfig = storedConfiguration;
      }
    } else {
      LocalStorage.remove(this.MOST_RECENT_CONFIG_KEY);
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
      let fileExtension = this.configFile.name.split('.').pop();
      const supportedExtensions = ['yml', 'yaml', 'json'];
      if (supportedExtensions.indexOf(fileExtension) !== -1) {
        this.importConfig = {
          fileName: this.configFile.name,
          fileContents: reader.result,
        };
        LocalStorage.set(this.MOST_RECENT_CONFIG_KEY, this.importConfig);
      }
      else {
        this.configFileError = true;
      }
    };
  }

  downloadResource() {
    this.importPending = true;
    this.notFoundError = false;
    this.serverError = undefined;
    this.xmlImportClient.getMetadataValues(this.resourceId, this.importConfig.fileContents, this.resourceKind)
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
  fileContents: string;
}
