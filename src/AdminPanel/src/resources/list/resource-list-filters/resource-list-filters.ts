export abstract class ResourceListFilters {
  inputBoxVisible: boolean;
  inputBoxFocused: boolean;

  abstract toggleInputBoxVisibility();

  abstract publishValue();

  protected showInputBoxAndSetFocusOnIt() {
    this.inputBoxVisible = true;
    this.inputBoxFocused = true;
  }

  protected removeFocusFromInputBoxAndHideIt() {
    this.inputBoxFocused = false;
    this.inputBoxVisible = false;
  }
}
