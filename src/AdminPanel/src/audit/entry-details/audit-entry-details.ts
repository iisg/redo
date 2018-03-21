export class AuditEntryDetails {
  entry;

  activate(model: Object) {
    $.extend(this, model);
  }
}
