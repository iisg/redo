import {DialogComponentActivate, DialogController} from "aurelia-dialog";
import {Metadata} from "resources-config/metadata/metadata";
import {autoinject} from "aurelia-dependency-injection";
import {filterByValues} from "common/utils/object-utils";
import {XmlImportProcessor} from "./xml-import-processor";

@autoinject
export class ImportConfirmationDialog implements DialogComponentActivate<ImportConfirmationDialogModel> {
  metadataList: Metadata[];
  resourceClass: string;

  acceptedValues: StringMap<any[]> = {};
  rejectedValues: StringMap<string[]> = {};
  extraValues: StringMap<string[]> = {};

  constructor(private dialogController: DialogController, private importProcessor: XmlImportProcessor) {
  }

  activate(model: ImportConfirmationDialogModel): void {
    this.metadataList = model.metadataList.filter(metadata => metadata.baseId >= 0);
    this.resourceClass = model.resourceClass;
    this.process(model.values, model.metadataList);
  }

  private process(valueMap: StringMap<string[]>, metadataList: Metadata[]) {
    const result = this.importProcessor.processValueMap(valueMap, metadataList);
    this.acceptedValues = result.acceptedValues;
    this.rejectedValues = result.rejectedValues;
    this.extraValues = result.extraValues;
  }

  confirm(): void {
    const values = filterByValues(this.acceptedValues, value => value !== undefined);
    this.dialogController.ok(values);
  }
}

export interface ImportConfirmationDialogModel {
  metadataList: Metadata[];
  values: StringMap<string[]>;
  resourceClass: string;
}
