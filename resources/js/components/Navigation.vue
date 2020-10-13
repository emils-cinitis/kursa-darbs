<template>
    <div>
        <b-navbar toggleable="lg" type="dark" variant="dark">
            <b-collapse id="nav-collapse" is-nav>
                <b-navbar-nav>
                    <b-nav-item>
                        <router-link :to="{ name: 'home' }">Home</router-link>
                    </b-nav-item>
                    <b-nav-item v-if="Object.keys(user).length">
                        <router-link :to="{ name: 'banners' }">Banners</router-link>
                    </b-nav-item>
                </b-navbar-nav>

                <b-navbar-nav class="ml-auto">
                    <b-nav-item v-if="!Object.keys(user).length">
                        <router-link :to="{ name: 'login' }">Login</router-link>
                    </b-nav-item>
                    <b-nav-item v-if="!Object.keys(user).length">
                        <router-link :to="{ name: 'register' }">Register</router-link>
                    </b-nav-item>
                    <b-nav-item-dropdown right v-if="Object.keys(user).length">
                        <template v-slot:button-content>
                            <em>User</em>
                        </template>
                            <b-dropdown-item>
                                <router-link :to="{ name: 'profile' }">Profile</router-link>
                            </b-dropdown-item>
                            <b-dropdown-item>
                                <a class="router-link-active" @click='logout'>Logout</a>
                            </b-dropdown-item>
                        </b-nav-item-dropdown>
                </b-navbar-nav>
            </b-collapse>
        </b-navbar>
    </div>
</template>

<script>
    export default {
        props: ['user'],
        watch: {
            user: function(){
                console.log(this.user);
            }
        },
        methods: {
            logout() {
                this.$auth.logout();
            }
        }
    }
</script>