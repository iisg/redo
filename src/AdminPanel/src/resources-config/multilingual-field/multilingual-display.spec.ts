import {MultilingualDisplay} from "./multilingual-display";
import {StageComponent, ComponentTester} from "aurelia-testing";
import {bootstrap} from "aurelia-bootstrapper";
import "jquery";

xdescribe(MultilingualDisplay.name, () => { // ignored due to https://github.com/aurelia/testing/issues/43

  let component: ComponentTester;
  let options = {theValue: {}};

  beforeEach(() => {
    component = StageComponent
      .withResources('src/resources-config/multilingual-field/multilingual-display')
      .inView('<multilingual-display value.bind="theValue"></multilingual-display>')
      .boundTo(options);
  });

  it('renders nothing if there is no value', done => {
    component.create(bootstrap as any).then(() => {
      expect($(".multilingual-display").children().length).toBe(0);
      done();
    });
  });

  it('renders value', done => {
    options.theValue = {
      PL: 'Polski',
      EN: 'English',
    };
    component.create(bootstrap as any).then(() => {
      expect($(".multilingual-display").children().length).toBe(2);
      expect($(".multilingual-display li:first-child").text().trim()).toBe(options.theValue['PL']);
      expect($(".multilingual-display li:nth-child(2)").text().trim()).toBe(options.theValue['EN']);
      done();
    });
  });

  afterEach(() => {
    component.dispose();
  });
});
