export class ControlLabelValueConverter implements ToViewValueConverter {
  private static readonly LABELS = {
    boolean: "Prawda / fałsz",
    date: "Data",
    integer: "Liczba całkowita",
    text: "Tekst",
    textarea: "Długi tekst",
  };

  toView(value: string): string {
    return ControlLabelValueConverter.LABELS[value] || value;
  }
}
