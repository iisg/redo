import {DialogController} from "aurelia-dialog";
import {autoinject} from "aurelia-dependency-injection";
import {XmlImportClient} from "./xml-import-repository";
import {observable} from "aurelia-binding";
import {XmlImportConfig, XmlImportConfigExecutor} from "./import-config";
import {XmlImportError} from "./import-config-evaluator";

@autoinject
export class ImportDialog {
  importConfig: XmlImportConfig;
  resourceId: string = '';

  @observable configFile: File = undefined;
  downloadPending: boolean = false;
  notFoundResourceId: string = undefined;
  configFileError: Error | XmlImportError = undefined;

  private readonly MOST_RECENT_CONFIG_KEY = 'mostRecentXmlImportConfig';

  constructor(private dialogController: DialogController, private xmlImportClient: XmlImportClient) {
    dialogController.settings.lock = false;
    const storedConfig = localStorage[this.MOST_RECENT_CONFIG_KEY];
    if (storedConfig !== undefined) {
      try {
        this.importConfig = JSON.parse(storedConfig);
      } catch (_) {
        // value is invalid, erase it
        delete localStorage[this.MOST_RECENT_CONFIG_KEY];
      }
    }
  }

  configFileChanged(): void {
    // Ideally, this should be done in a value converter, but FileReader is asynchronous and value converters are not
    const reader = new FileReader();
    reader.readAsText(this.configFile);
    reader.onload = () => {
      this.configFileError = undefined;
      this.importConfig = undefined;
      try {
        this.importConfig = JSON.parse(reader.result);
        this.importConfig.fileName = this.configFile.name;
        localStorage[this.MOST_RECENT_CONFIG_KEY] = JSON.stringify(this.importConfig);
      } catch (e) {
        // instanceof doesn't work for classes extending Error and hacks that set prototype in constructor make Karma tests fail silently!
        this.configFileError = ('replacements' in e) ? e : new Error("File is not a valid import configuration");
      }
    };
  }

  downloadResource() {
    this.downloadPending = true;
    this.notFoundResourceId = undefined;
    this.configFileError = undefined;
    this.xmlImportClient.get(this.resourceId)
      .then(xml => {
        this.tryImport(xml);
      })
      .catch(() => this.notFoundResourceId = this.resourceId)
      .finally(() => this.downloadPending = false);
  }

  private tryImport(xml: XMLDocument) {
    try {
      const importedValues: StringMap<string[]> = new XmlImportConfigExecutor(this.importConfig).execute(xml);
      this.dialogController.ok(importedValues);
    } catch (e) {
      this.configFileError = e;
    }
  }
}

export class SingleFileListValueConverter implements FromViewValueConverter {
  fromView(fileList: FileList): File {
    return fileList.length > 0 ? fileList.item(0) : undefined;
  }
}
