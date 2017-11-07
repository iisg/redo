import {ImportConfigEvaluator, XmlImportError} from "./import-config-evaluator";

export interface XmlImportConfig {
  transforms: StringMap<XmlConfigTransform>;
  mappings: StringMap<XmlConfigMapping>;
  fileName: string;
}

export class XmlImportConfigExecutor {
  transforms: StringMap<XmlConfigTransform>;
  mappings: StringMap<XmlConfigMapping>;

  constructor(config: { transforms: StringMap<XmlConfigTransform>, mappings: StringMap<XmlConfigMapping> }) {
    this.transforms = config.transforms;
    this.mappings = config.mappings;

    for (const key in this.transforms) {
      const bareTransform = this.transforms[key];
      this.transforms[key] = new XmlConfigTransform(bareTransform);
    }

    for (const key in this.mappings) {
      const bareMapping = this.mappings[key];
      this.mappings[key] = new XmlConfigMapping(bareMapping.selector, bareMapping.value);
    }
  }

  execute(xml: XMLDocument): ElementContents {
    const evaluator = new ImportConfigEvaluator(this.transforms);
    const result = {};
    for (const metadataId in this.mappings) {
      const mapping = this.mappings[metadataId];
      result[metadataId + ''] = evaluator.evaluate(mapping.getFields(xml), mapping.value);
    }
    return result;
  }
}

export class XmlConfigTransform implements XmlConfigTransform {
  regex?: string;       // replacement transforms
  replacement?: string; // replacement transforms
  glue?: string;        // join transforms

  constructor(initialValues: XmlConfigTransform) {
    this.regex = initialValues.regex;
    this.replacement = initialValues.replacement;
    this.glue = initialValues.glue;
  }

  apply(values: string[]): string[] {
    if (this.regex !== undefined && this.replacement !== undefined) {
      values = values.map(str => str.replace(new RegExp(this.regex, 'g'), this.replacement));
    }
    if (this.glue !== undefined) {
      values = [values.join(this.glue)];
    }
    return values;
  }
}

export class XmlConfigMapping {
  selector: string;
  value: string[];

  private readonly fieldPattern = /^[a-z][a-zA-Z\d]*$|^(?:[a-z][a-zA-Z\d]*)?(\[(?:tag=\d+|ind[12]=[\d ])])+$/;

  constructor(selector: string, value: string[]|string) {
    this.validateFieldFormat(selector);
    this.selector = selector;
    this.value = Array.isArray(value) ? value as string[] : [value as string];
  }

  private validateFieldFormat(field: string) {
    if (this.fieldPattern.exec(field) == undefined) {
      throw new XmlImportError('Invalid field format {{format}}', {format: field});
    }
  }

  getFields(xml: XMLDocument): ElementContents[] {
    const results: ElementContents[] = [];
    $(this.selector, xml).each((_, element: Element) => {
      results.push(this.getElementContents(element));
    });
    return results;
  }

  private getElementContents(element: Element): ElementContents {
    return (element.nodeName == 'datafield')
      ? this.getDatafieldContents(element)
      : this.getFlatElementContents(element);
  }

  private getDatafieldContents(datafield: Element): ElementContents {
    const subfields: ElementContents = {};
    $(datafield).children('subfield').each((_, subfield: Element) => {
      const $subfield = $(subfield);
      const code = $subfield.attr('code');
      if (!(code in subfields)) {
        subfields[code] = [];
      }
      subfields[code].push($subfield.text());
    });
    return subfields;
  }

  private getFlatElementContents(element: Element): ElementContents {
    return {'*': [$(element).text()]};
  }
}

export type ElementContents = StringMap<string[]>;
