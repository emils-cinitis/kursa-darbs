import Vue from 'vue'

import 'es6-promise/auto'
import axios from 'axios'

import VueAuth from '@websanova/vue-auth'
import VueAxios from 'vue-axios'
import VueRouter from 'vue-router'
import { BootstrapVue } from 'bootstrap-vue'

import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

import auth from './auth'
import router from './router';

Vue.router = router
Vue.use(VueRouter)
Vue.use(BootstrapVue)

Vue.use(VueAxios, axios)
axios.defaults.baseURL = `http://localhost:8000/api`//`https://tt2.mobinet.lv/api`

Vue.use(VueAuth, auth)

Vue.component('App', require('./App.vue').default);

const app = new Vue({
    el: '#app',
    router
});