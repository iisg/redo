import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class GoToLinkOnRowClickCustomAttribute {
  private static readonly MAX_CLICK_MOVE = 10;

  value: string;
  private lastMouseDownPosition: { x: number, y: number } = {x: 0, y: 0};

  constructor(element: Element) {
    $(element).on('mousedown', (event: JQueryEventObject) => this.onMouseDown(event));
    $(element).on('mouseup', (event: JQueryEventObject) => this.onMouseUp(event));
  }

  private onMouseDown(event: JQueryEventObject) {
    if (this.isLeftMouseClick(event)) {
      this.lastMouseDownPosition = {x: event.pageX, y: event.pageY};
    }
  }

  private onMouseUp(event: JQueryEventObject) {
    if (this.isLeftMouseClick(event)) {
      const currentPosition = {x: event.pageX, y: event.pageY};
      const moveX = Math.abs(currentPosition.x - this.lastMouseDownPosition.x);
      const moveY = Math.abs(currentPosition.y - this.lastMouseDownPosition.y);
      if (moveX <= GoToLinkOnRowClickCustomAttribute.MAX_CLICK_MOVE && moveY <= GoToLinkOnRowClickCustomAttribute.MAX_CLICK_MOVE) {
        if (this.notAButton(event.target)) {
          const link = $(event.currentTarget).find(this.linkSelector)[0];
          setTimeout(() => link.click());
        }
      }
    }
  }

  private isLeftMouseClick(event: JQueryEventObject): boolean {
    return event.which == 1;
  }

  private notAButton(element: Element): boolean {
    return !$(element).is('a, button') && !$(element).parents('a, button').length;
  }

  private get linkSelector(): string {
    return this.value || 'a, button';
  }
}