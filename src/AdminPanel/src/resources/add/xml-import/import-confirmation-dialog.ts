import {DialogComponentActivate, DialogController} from "aurelia-dialog";
import {Metadata} from "resources-config/metadata/metadata";
import {autoinject} from "aurelia-dependency-injection";
import {filterByValues} from "common/utils/object-utils";
import {ImportResult} from "./xml-import-client";

@autoinject
export class ImportConfirmationDialog implements DialogComponentActivate<ImportConfirmationDialogModel> {
  metadataList: Metadata[];
  resourceClass: string;
  importResult: ImportResult;

  constructor(private dialogController: DialogController) {
  }

  activate(model: ImportConfirmationDialogModel): void {
    this.metadataList = model.metadataList.filter(metadata => metadata.id >= 0);
    this.resourceClass = model.resourceClass;
    this.importResult = model.importResult;
  }

  confirm(): void {
    const values = filterByValues(this.importResult.acceptedValues, value => value !== undefined);
    this.dialogController.ok(values);
  }
}

export interface ImportConfirmationDialogModel {
  metadataList: Metadata[];
  importResult: ImportResult;
  invalidMetadataKeys: string[];
  resourceClass: string;
}
