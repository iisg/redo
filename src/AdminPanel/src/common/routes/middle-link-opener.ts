import {RouterConfiguration} from "aurelia-router";

// temporary fix for https://github.com/aurelia/router/issues/457
export function supportMiddleClickInLinks(config: RouterConfiguration) {
  const addBaseUrlIfMiddleClick = (event: JQueryEventObject) => {
    if (event.ctrlKey || event.which == 2) {
      const link = $(event.currentTarget);
      const originalHref = link.attr('href');
      link.attr('href', config.options.root + originalHref);
      setTimeout(() => link.attr('href', originalHref));
    }
  };
  $(document).arrive("a[route-href]", function (newLink) {
    $(newLink).mouseup(addBaseUrlIfMiddleClick);
  });
}
