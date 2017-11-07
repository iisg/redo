import {XmlConfigTransform, ElementContents} from "./import-config";

export class ImportConfigEvaluator {
  private transforms: StringMap<XmlConfigTransform>;

  constructor(transforms: StringMap<XmlConfigTransform>) {
    this.transforms = transforms;
  }

  evaluate(fields: ElementContents[], parts: string[]): string[] {
    return fields.map(field => {
      return parts.map(part => this.evaluateExpression(field, part)).join('');
    });
  }

  private evaluateExpression(subfields: ElementContents, expression: string): string {
    const result: ParsingResult = (expression[0] == "'")
      ? this.parseStringLiteral(expression)
      : this.parseSubfield(expression, subfields);
    let values: string[] = result.parsedValues;
    let transforms = result.unparsedInput.trim();
    while (transforms.length > 0) {
      if (transforms.trim()[0] == '|') {
        const nextPipeIndex = transforms.indexOf('|', 1);
        const transformNameEnd = (nextPipeIndex != -1) ? nextPipeIndex : transforms.length;
        const transformName = transforms.substring(1, transformNameEnd).trim();
        if (!(transformName in this.transforms)) {
          throw new XmlImportError('Unknown transform {{name}}', {name: transformName});
        }
        values = this.transforms[transformName].apply(values);
        transforms = transforms.substr(transformNameEnd);
      } else {
        throw new XmlImportError('Invalid expression {{expression}}', {expression: expression});
      }
    }
    return values.join('');
  }

  private parseStringLiteral(input: string): ParsingResult {
    const originalInput = input;
    input = input.substr(1);  // strip quotation mark
    let literal = '';
    while (true) {
      const quotationPos = input.indexOf("'");
      if (quotationPos == -1) {
        throw new XmlImportError('Unclosed string literal {{literal}}', {literal: originalInput});
      }
      const quotedString = input.substr(0, quotationPos);
      input = input.substr(quotationPos + 1);
      if (quotedString[quotedString.length - 1] == '\\') {
        // it's an escaped quotation mark, does not mark end of string
        literal += quotedString.substr(0, quotedString.length - 1) + "'";
      } else {
        literal += quotedString;
        return {
          parsedValues: [literal],
          unparsedInput: input
        };
      }
    }
  }

  private parseSubfield(input: string, subfields: ElementContents): ParsingResult {
    const matches = input.match(/^[a-z0-9*]+/i);
    if (matches == undefined) {
      throw new XmlImportError('Invalid subfield format {{format}}', {format: input});
    }
    const fieldName = matches[0];
    if (!(fieldName in subfields)) {
      throw new XmlImportError('Missing subfield {{name}}', {name: fieldName});
    }
    return {
      parsedValues: subfields[fieldName],
      unparsedInput: input.substr(fieldName.length)
    };
  }
}

interface ParsingResult {
  parsedValues: string[];
  unparsedInput: string;
}

export class XmlImportError extends Error {
  constructor(message: string, public replacements: {}) {
    super(message);
  }
}
