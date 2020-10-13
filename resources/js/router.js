import VueRouter from 'vue-router'

import Home from './views/Home.vue'
import About from './views/About.vue'
import Register from './views/Register.vue'
import Login from './views/Login.vue'
//import BannerIndex from './views/Banners/Index.vue'
import ProfileIndex from './views/Profile/Index.vue'
import ProfileEdit from './views/Profile/Edit.vue'

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
        path: '/about',
        name: 'about',
        component: About,
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
            }
        ]
    },
    // ({
    //     path: '/banners',
    //     name: 'banners',
    //     component: BannerIndex,
    //     meta: {
    //         auth: true
    //     }
    // },
];

const router = new VueRouter({
    history: true,
    mode: 'history',
    routes,
  })
  export default router