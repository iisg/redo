export class AuditEntryDetails {
  entry: any;

  activate(model: Object) {
    $.extend(this, model);
  }
}
