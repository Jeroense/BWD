
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
require('./bootstrap');
require('./sideBar');
require('./upload');
require('./showProductDetail');
// require('./customization');
require('./getImage');
// require('./setActiveImage');

import Vue from 'vue';

// customization
import Buefy from 'buefy';
Vue.use(Buefy);

// import VeeValidate from 'vee-validate';
// Vue.use(VeeValidate);

// const app = new Vue({
//     el: '#app'
// });
