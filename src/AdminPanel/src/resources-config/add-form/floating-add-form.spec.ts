import {FloatingAddForm} from "./floating-add-form";

describe(FloatingAddForm.name, () => {
  it("initializes the controller", () => {
    let form = new FloatingAddForm();
    form.controller = {};
    form.attached();
    expect(form.controller.hide).toBeDefined();
  });

  it("ignores situation when there is no controller specified", () => {
    let form = new FloatingAddForm();
    form.attached();
    expect(form.controller).toBeUndefined();
  });

  it("hides the form when controller hide is called", () => {
    let form = new FloatingAddForm();
    form.controller = {};
    form.attached();
    form.formOpened = true;
    form.controller.hide();
    expect(form.formOpened).toBeFalsy();
  });
});
