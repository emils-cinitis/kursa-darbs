<template>
    <div>
        <b-navbar toggleable="lg">
            <b-collapse id="nav-collapse" is-nav>
                <b-navbar-nav class="w-100">
                    <router-link 
                        :to="{ name: 'home' }"
                        class="nav-link"
                    >Home</router-link>
                    
                    <div class="d-flex mx-auto">
                        <router-link 
                            v-if="Object.keys(user).length"
                            class="nav-link" 
                            :to="{ name: 'all-banners' }"
                        >My banners</router-link>

                        <router-link 
                            v-if="Object.keys(user).length"
                            class="nav-link"
                            :to="{ name: 'all-color-schemes' }"
                        >My color schemes</router-link>
                        
                        <router-link 
                            v-if="Object.keys(user).length"
                            class="nav-link" 
                            :to="{ name: 'all-templates' }"
                        >My templates</router-link>
                    </div>
                </b-navbar-nav>

                <b-navbar-nav class="ml-auto">
                    <b-nav-item v-if="!Object.keys(user).length">
                        <a class="router-link-active" @click="$bvModal.show('loginModal')">Login</a>
                    </b-nav-item>
                    <b-nav-item v-if="!Object.keys(user).length">
                        <a class="router-link-active" @click="$bvModal.show('registerModal')">Register</a>
                    </b-nav-item>
                    <b-nav-item-dropdown right v-if="Object.keys(user).length">
                        <template v-slot:button-content>
                            <span>{{user.username}}</span>
                        </template>
                            <li>
                                <router-link class="dropdown-item" :to="{ name: 'profile' }">Profile</router-link>
                            </li>
                            <li>
                                <a class="dropdown-item" @click='logout' href="#">Logout</a>
                            </li>
                        </b-nav-item-dropdown>
                </b-navbar-nav>
            </b-collapse>
        </b-navbar>
    </div>
</template>

<script>
    export default {
        props: ['user'],
        methods: {
            logout() {
                this.$auth.logout();
            }
        }
    }
</script>