// TODO replace this with an Aurelia backend once it's released: https://github.com/aurelia/i18n/issues/30#issuecomment-269768179
export function translationLoader(url, options, callback, data) {
  SystemJS.import(url + '!text').then(json => {
    callback(json, {status: '200'});
  }).catch(() => {
    callback(undefined, {status: '404'});
  });
}
