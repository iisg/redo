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
    if (this.isLeftOrMiddleMouseClick(event)) {
      this.lastMouseDownPosition = {x: event.pageX, y: event.pageY};
    }
  }

  private onMouseUp(event: JQueryEventObject) {
    if (this.isLeftOrMiddleMouseClick(event) && this.isClickingOnRowEnabled(event)) {
      const currentPosition = {x: event.pageX, y: event.pageY};
      const moveX = Math.abs(currentPosition.x - this.lastMouseDownPosition.x);
      const moveY = Math.abs(currentPosition.y - this.lastMouseDownPosition.y);
      if (moveX <= GoToLinkOnRowClickCustomAttribute.MAX_CLICK_MOVE && moveY <= GoToLinkOnRowClickCustomAttribute.MAX_CLICK_MOVE) {
        if (this.notAButton(event.target)) {
          const link = $(event.currentTarget).find(this.linkSelector)[0];
          if (link) {
            setTimeout(() => {
              link.dispatchEvent(new MouseEvent("mouseup", event.originalEvent));
              link.dispatchEvent(new MouseEvent("click", event.originalEvent));
            });
          }
        }
      }
    }
  }

  private isLeftOrMiddleMouseClick(event: JQueryEventObject): boolean {
    return event.which == 1 || event.which == 2;
  }

  private isClickingOnRowEnabled(event: JQueryEventObject): boolean {
    return !event.currentTarget.classList.contains('go-to-link-on-row-click-disabled');
  }

  private notAButton(element: Element): boolean {
    return !$(element).is('a, button') && !$(element).parents('a, button').length;
  }

  private get linkSelector(): string {
    return this.value || 'a, button';
  }
}
