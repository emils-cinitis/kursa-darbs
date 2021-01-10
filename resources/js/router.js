import VueRouter from 'vue-router';

import Home from './views/Home.vue';
import Register from './views/Register.vue';
import Login from './views/Login.vue';
import ProfileIndex from './views/Profile/Index.vue';
import ProfileEdit from './views/Profile/Edit.vue';
import ProfileDelete from './views/Profile/Delete.vue';
import BannerIndex from './views/Banners/Index.vue';
import BannerAll from './views/Banners/ShowAll.vue';
import BannerForm from './views/Banners/Form.vue';
import ColorSchemeIndex from './views/ColorSchemes/Index.vue';
import ColorSchemesAll from './views/ColorSchemes/ShowAll.vue';
import ColorSchemesForm from './views/ColorSchemes/Form.vue';
import TemplateIndex from './views/Templates/Index.vue';
import TemplatesAll from './views/Templates/ShowAll.vue';
import TemplatesForm from './views/Templates/Form.vue';
import PublicBanner from './views/Public/Banner.vue';
import AllPublicBanners from './views/Public/AllBanners.vue';
import AdminIndex from './views/Administrator/Index.vue';
import AdminAllUsers from './views/Administrator/ShowUsers.vue';
import AdminShowUser from './views/Administrator/User.vue';
import AdminShowBanners from './views/Administrator/ShowBanners.vue';
import AdminShowBanner from './views/Administrator/Banner.vue';
import BlockedUser from './views/BlockedUser.vue';
import ResetPasswordConfirmation from './views/ResetPasswordConfirmation.vue';

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
        path: '/banner/:uuid',
        name: 'public-banner',
        component: PublicBanner
    },
    {
        path: '/public-banners/:page?',
        name: 'public-banners',
        component: AllPublicBanners
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
        path: '/user/banners',
        name: 'banners',
        component: BannerIndex,
        meta: {
            auth: {
                roles: [1, 2, 3], 
                redirect: { name: 'login' }, 
                forbiddenRedirect: { name: 'blocked-user' }
            }
        },
        children: [
            {
                path: 'all',
                name: 'all-banners',
                component: BannerAll,
                meta: {
                    auth: {
                        roles: [1, 2, 3], 
                        redirect: { name: 'login' }, 
                        forbiddenRedirect: { name: 'blocked-user' }
                    }
                },
            },
            {
                path: 'add',
                name: 'add-banner',
                component: BannerForm,
                meta: {
                    auth: {
                        roles: [2, 3], 
                        redirect: { name: 'login' }, 
                        forbiddenRedirect: { name: 'blocked-user' }
                    }
                },
            },
            {
                path: 'edit/:uuid',
                name: 'edit-banner',
                component: BannerForm,
                meta: {
                    auth: {
                        roles: [2, 3], 
                        redirect: { name: 'login' }, 
                        forbiddenRedirect: { name: 'blocked-user' }
                    }
                }
            }
        ]
    },
    {
        path: '/color-schemes',
        name: 'color-schemes',
        component: ColorSchemeIndex,
        meta: {
            auth: {
                roles: [2, 3], 
                //redirect: { name: 'login' }, 
                forbiddenRedirect: { name: 'blocked-user' }
            }
        },
        children: [
            {
                path: 'all',
                name: 'all-color-schemes',
                component: ColorSchemesAll,
                meta: {
                    auth: {
                        roles: [2, 3], 
                        //redirect: { name: 'login' }, 
                        forbiddenRedirect: { name: 'blocked-user' }
                    }
                },
            },
            {
                path: 'add',
                name: 'add-color-scheme',
                component: ColorSchemesForm,
                meta: {
                    auth: {
                        roles: [2, 3], 
                        //redirect: { name: 'login' }, 
                        forbiddenRedirect: { name: 'blocked-user' }
                    }
                },
            },
            {
                path: 'edit/:id',
                name: 'edit-color-scheme',
                component: ColorSchemesForm,
                meta: {
                    auth: {
                        roles: [2, 3], 
                        //redirect: { name: 'login' }, 
                        forbiddenRedirect: { name: 'blocked-user' }
                    }
                }
            }
        ]
    },
    {
        path: '/tempaltes',
        name: 'tempaltes',
        component: TemplateIndex,
        meta: {
            auth: {
                roles: [2, 3], 
                redirect: { name: 'login' }, 
                forbiddenRedirect: { name: 'blocked-user' }
            }
        },
        children: [
            {
                path: 'all',
                name: 'all-templates',
                component: TemplatesAll,
                meta: {
                    auth: {
                        roles: [2, 3], 
                        redirect: { name: 'login' }, 
                        forbiddenRedirect: { name: 'blocked-user' }
                    }
                },
            },
            {
                path: 'add',
                name: 'add-template',
                component: TemplatesForm,
                meta: {
                    auth: {
                        roles: [2, 3], 
                        redirect: { name: 'login' }, 
                        forbiddenRedirect: { name: 'blocked-user' }
                    }
                },
            },
            {
                path: 'edit/:id',
                name: 'edit-template',
                component: TemplatesForm,
                meta: {
                    auth: {
                        roles: [2, 3], 
                        redirect: { name: 'login' }, 
                        forbiddenRedirect: { name: 'blocked-user' }
                    }
                }
            }
        ]
    },
    {
        path: '/admin',
        name: 'admin-dashboard',
        component: AdminIndex,
        meta: {
            auth: {
                roles: 3, 
                redirect: { name: 'login' }, 
                forbiddenRedirect: '/'
            }
        },
        children: [
            {
                path: 'users/:page?',
                name: 'admin-all-users',
                component: AdminAllUsers,
                meta: {
                    auth: {
                        roles: 3, 
                        redirect: { name: 'login' }, 
                        forbiddenRedirect: '/'
                    }
                },
            },
            {
                path: 'banners/:user_uuid?/:page?',
                name: 'admin-all-banners',
                component: AdminShowBanners,
                meta: {
                    auth: {
                        roles: 3, 
                        redirect: { name: 'login' }, 
                        forbiddenRedirect: '/'
                    }
                },
            },
            {
                path: 'user/:uuid',
                name: 'admin-show-user',
                component: AdminShowUser,
                meta: {
                    auth: {
                        roles: 3, 
                        redirect: { name: 'login' }, 
                        forbiddenRedirect: '/'
                    }
                },
            },
            {
                path: 'banner/:uuid',
                name: 'admin-show-banner',
                component: AdminShowBanner,
                meta: {
                    auth: {
                        roles: 3, 
                        redirect: { name: 'login' }, 
                        forbiddenRedirect: '/'
                    }
                },
            },
        ]
    },
    {
        path: '/reset-password/:uuid',
        name: 'reset-password-confirmation',
        component: ResetPasswordConfirmation,
        meta: {
            auth: false
        }
    },
    {
        path: '/blocked-user',
        name: 'blocked-user',
        component: BlockedUser,
        meta: {
            auth: {
                roles: 1, 
                redirect: '/', 
                forbiddenRedirect: '/'
            }
        }
    },
    {
        path: '/404',
        name: '404',
        component: Home
    }
];

const router = new VueRouter({
    history: true,
    mode: 'history',
    routes,
});

router.beforeEach((to, from, next) => {
    if (!to.matched.length) {
        next('/404');
    } else {
        next();
    }
});

export default router;