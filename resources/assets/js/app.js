
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
require('./bootstrap');
require('./sideBar');
require('./upload');
require('./showProductDetail');
require('./customization');

// customization
import Buefy from 'buefy';

Vue.use(Buefy);
