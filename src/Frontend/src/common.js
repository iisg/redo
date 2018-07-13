import "expose-loader?jQuery!expose-loader?$!jquery";
import "bootstrap";
import "bootstrap/dist/css/bootstrap.min.css";
import Vue from "vue";
// import VueI18N from "vue-i18n";
import VueResource from "vue-resource";
import "style-loader!css-loader!sass-loader!./styles/styles.scss";

// Vue.use(VueI18N);
Vue.use(VueResource);

Vue.config.external = window.FRONTEND_CONFIG || {};
if (!Vue.config.external.baseUrl) {
  Vue.config.external.baseUrl = '';
}
Vue.http.options.root = Vue.config.external.baseUrl + '/api';

const components = {
  LoginPage: () => import("./login/login-page"),
  RedoLogo: () => import("./common/redo-logo"),
  RepekaVersion: () => import("./common/repeka-version"),
};

$(document).ready(() => {
  if ($('.vue-container').length) {
    Vue.prototype.$user = Vue.config.external.user;
    new Vue({
      el: '.vue-container',
      // i18n,
      components,
    });
  }
});
