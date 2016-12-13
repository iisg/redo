import {BootstrapValidationRenderer} from "./bootstrap-validation-renderer";
import {RenderInstruction, ResultInstruction, ValidateResult} from "aurelia-validation";
import "jquery";

describe(BootstrapValidationRenderer.name, () => {
  let renderer: BootstrapValidationRenderer;
  let html: JQuery;

  class TestRenderInstruction implements RenderInstruction {
    kind;
    render: ResultInstruction[] = [];
    unrender: ResultInstruction[] = [];

    addRender(errorId: number, elementSelector: string): TestRenderInstruction {
      this.render.push(TestRenderInstruction.newResultInstruction(errorId, elementSelector));
      return this;
    }

    addUnrender(errorId: number, elementSelector: string): TestRenderInstruction {
      this.unrender.push(TestRenderInstruction.newResultInstruction(errorId, elementSelector));
      return this;
    }

    private static newResultInstruction(errorId: number, elementSelector: string) {
      return <ResultInstruction>{
        result: TestRenderInstruction.newError(errorId),
        elements: [html.find(`${elementSelector} input`)[0]]
      };
    };

    private static newError(id: number): ValidateResult {
      let error = new ValidateResult({}, {}, undefined, false, `Error ${id}`);
      error.id = id;
      return error;
    }
  }

  beforeEach(() => {
    html = $(`<form>
                  <div class="form-group first"><input type="text"></div>
                  <div class="form-group second"><input type="text"></div>
              </form>`);
    renderer = new BootstrapValidationRenderer;
  });

  it('displays an error', () => {
    renderer.render(new TestRenderInstruction().addRender(1, '.first'));
    expect(html.find(".first").hasClass('has-error')).toBeTruthy();
    expect(html.find(".second").hasClass('has-error')).toBeFalsy();
    expect(html.find(".first .help-block").length).toBe(1);
  });

  it('clears an error', () => {
    renderer.render(new TestRenderInstruction().addRender(1, '.first'));
    renderer.render(new TestRenderInstruction().addUnrender(1, '.first'));
    expect(html.find(".first").hasClass('has-error')).toBeFalsy();
    expect(html.find(".first .help-block").length).toBe(0);
  });

  it('renders many errors', () => {
    renderer.render(new TestRenderInstruction().addRender(1, '.first').addRender(2, '.first'));
    expect(html.find(".first").hasClass('has-error')).toBeTruthy();
    expect(html.find(".first .help-block").length).toBe(2);
  });

  it('renders errors next to desired fields', () => {
    renderer.render(new TestRenderInstruction().addRender(2, '.second').addRender(1, '.first'));
    expect(html.find(".first .help-block").text()).toBe('Error 1');
    expect(html.find(".second .help-block").text()).toBe('Error 2');
  });

  it('clears single error', () => {
    renderer.render(new TestRenderInstruction().addRender(1, '.first').addRender(2, '.first'));
    renderer.render(new TestRenderInstruction().addUnrender(1, '.first'));
    expect(html.find(".first").hasClass('has-error')).toBeTruthy();
    expect(html.find(".first .help-block").length).toBe(1);
    expect(html.find(".first .help-block").text()).toBe('Error 2');
  });
});
