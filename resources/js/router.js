import VueRouter from 'vue-router'

import Home from './views/Home.vue'
import Register from './views/Register.vue'
import Login from './views/Login.vue'
import ProfileIndex from './views/Profile/Index.vue'
import ProfileEdit from './views/Profile/Edit.vue'
import ProfileDelete from './views/Profile/Delete.vue'
import BannerIndex from './views/Banners/Index.vue'
import BannerAll from './views/Banners/ShowAll.vue'
import BannerForm from './views/Banners/Form.vue'
import ColorSchemeIndex from './views/ColorSchemes/Index.vue';
import ColorSchemesAll from './views/ColorSchemes/ShowAll.vue';
import ColorSchemesForm from './views/ColorSchemes/Form.vue';

const routes = [
    {
        path: '/',
        name: 'home',
        component: Home,
        meta: {
            auth: undefined
        }
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: {
            auth: false
        }
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: {
            auth: false
        }
    },
    {
        path: '/profile',
        name: 'profile',
        component: ProfileIndex,
        meta: {
            auth: true
        },
        children: [
            {
                path: 'edit',
                name: 'edit-user',
                component: ProfileEdit,
                meta: {
                    auth: true
                },
            },
            {
                path: 'delete',
                name: 'delete-user',
                component: ProfileDelete,
                meta: {
                    auth: true
                },
            }
        ]
    },
    {
        path: '/banners',
        name: 'banners',
        component: BannerIndex,
        meta: {
            auth: true
        },
        children: [
            {
                path: 'all',
                name: 'all-banners',
                component: BannerAll,
                meta: {
                    auth: true
                },
            },
            {
                path: 'add',
                name: 'add-banner',
                component: BannerForm,
                meta: {
                    auth: true
                },
            },
            {
                path: 'edit/:uuid',
                name: 'edit-banner',
                component: BannerForm,
                meta: {
                    auth: true
                }
            }
            /*{
                path: 'edit',
                name: 'edit-banner',
                component: BannerEdit,
                meta: {
                    auth: true
                },
            }*/
        ]
    },
    {
        path: '/color-schemes',
        name: 'color-schemes',
        component: ColorSchemeIndex,
        meta: {
            auth: true
        },
        children: [
            {
                path: 'all',
                name: 'all-color-schemes',
                component: ColorSchemesAll,
                meta: {
                    auth: true
                },
            },
            {
                path: 'add',
                name: 'add-color-scheme',
                component: ColorSchemesForm,
                meta: {
                    auth: true
                },
            },
            {
                path: 'edit/:id',
                name: 'edit-color-scheme',
                component: ColorSchemesForm,
                meta: {
                    auth: true
                }
            }
            /*{
                path: 'edit',
                name: 'edit-banner',
                component: BannerEdit,
                meta: {
                    auth: true
                },
            }*/
        ]
    },
];

const router = new VueRouter({
    history: true,
    mode: 'history',
    routes,
  })
  export default router