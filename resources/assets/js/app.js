
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('./sideBar');
// window.Vue = require('Vue');
// window.Vue = require('Vue');
import Buefy from 'buefy';
// import 'buefy/lib/buefy.css';

Vue.use(Buefy);
require('./sideBar');

// Setup Vue object
// var app = new Vue({
//     el: '#app',
//     data: {
//         permissionType: '',
//     }
// });

