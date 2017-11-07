import {DialogConfiguration} from "aurelia-dialog";

export function dialogConfigurator(dialog: DialogConfiguration) {
  dialog.useDefaults();
  dialog.settings.lock = false;
  dialog.settings.keyboard = false;
  dialog.settings.centerHorizontalOnly = true;
}
