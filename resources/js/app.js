require('./bootstrap');
window.Vue = require("vue");

import Vuetify from 'vuetify';

import store from './store'

//Main pages
import App from './components/App'


const app = new Vue({
    el: '#app',
    store,
    vuetify: Vuetify,
    components: { App }
});
