import {DialogComponentActivate, DialogController} from "aurelia-dialog";
import {Metadata} from "resources-config/metadata/metadata";
import {autoinject} from "aurelia-dependency-injection";
import {filterByValues} from "common/utils/object-utils";
import {ImportResult} from "./xml-import-client";
import {ResourceKind} from "../../../resources-config/resource-kind/resource-kind";

@autoinject
export class ImportConfirmationDialog implements DialogComponentActivate<ImportConfirmationDialogModel> {
  metadataList: Metadata[];
  resourceKind: ResourceKind;
  resourceClass: string;
  importResult: ImportResult;

  constructor(private dialogController: DialogController) {
  }

  activate(model: ImportConfirmationDialogModel): void {
    this.metadataList = model.metadataList.filter(metadata => metadata.id >= 0);
    this.resourceClass = model.resourceClass;
    this.importResult = model.importResult;
    this.resourceKind = model.resourceKind;
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
  resourceKind: ResourceKind;
  resourceClass: string;
}
