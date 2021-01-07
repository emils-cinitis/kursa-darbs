<template>
    <div class="block-with-sidebar">
        <div v-if="exception == ''">
            <h1>All Users</h1>
            <b-row>
                <!-- Name filter -->
                <b-col cols="4">
                    <b-form-group
                        label="Name:"
                        label-for="name"
                    >
                        <b-form-input 
                            id="name"
                            type="text"
                            v-model="user_filter.name"
                        />
                    </b-form-group>
                </b-col>

                <!-- Email filter -->
                <b-col cols="4">
                    <b-form-group
                        label="Email:"
                        label-for="email"
                    >
                        <b-form-input 
                            id="email"
                            type="text"
                            v-model="user_filter.email"
                        />
                    </b-form-group>
                </b-col>

                <!-- Role filter -->
                <b-col cols="4" v-if="user_roles !== null">
                    <b-form-group
                        label="Role:"
                        label-for="role"
                    >
                        <b-form-select 
                            id="role"
                            v-model="user_filter.role" 
                            :options="user_roles">
                        </b-form-select>
                    </b-form-group>
                </b-col>

                <b-col cols="12">
                </b-col>

                <!-- Color scheme filters -->
                <b-col cols="2">
                    <b-form-group
                        label="Min color schemes:"
                        label-for="min_color_schemes"
                    >
                        <b-form-input 
                            id="min_color_schemes"
                            type="number"
                            v-model="user_filter.color_scheme_min"
                        />
                    </b-form-group>
                </b-col>
                <b-col cols="2">
                    <b-form-group
                        label="Max color schemes:"
                        label-for="max_color_schemes"
                    >
                        <b-form-input 
                            id="max_color_schemes"
                            type="number"
                            v-model="user_filter.color_scheme_max"
                        />
                    </b-form-group>
                </b-col>

                <!-- Template filters -->
                <b-col cols="2">
                    <b-form-group
                        label="Min templates:"
                        label-for="min_templates"
                    >
                        <b-form-input 
                            id="min_templates"
                            type="number"
                            v-model="user_filter.template_min"
                        />
                    </b-form-group>
                </b-col>
                <b-col cols="2">
                    <b-form-group
                        label="Max templates:"
                        label-for="max_templates"
                    >
                        <b-form-input 
                            id="max_templates"
                            type="number"
                            v-model="user_filter.template_max"
                        />
                    </b-form-group>
                </b-col>

                <!-- Banner filters -->
                <b-col cols="2">
                    <b-form-group
                        label="Min banners:"
                        label-for="min_banners"
                    >
                        <b-form-input 
                            id="min_banners"
                            type="number"
                            v-model="user_filter.banner_min"
                        />
                    </b-form-group>
                </b-col>
                <b-col cols="2">
                    <b-form-group
                        label="Max banners:"
                        label-for="max_banners"
                    >
                        <b-form-input 
                            id="max_banners"
                            type="number"
                            v-model="user_filter.banner_max"
                        />
                    </b-form-group>
                </b-col>
            </b-row>
            <div v-if="users.length > 0">
                <!-- Show all filtered users -->
                <div class="table-container mx-auto" v-if="users.length > 0">
                    <b-row v-for="(user, key) in users" :key="key" class="table-row">
                        <b-col cols="12">
                            <b-row>
                                <b-col cols="8">
                                    <b-row>
                                        <b-col cols="6">
                                            <b-row>
                                                <b-col cols="12">
                                                    <span>Username: <b>{{ user.username }}</b></span>
                                                </b-col>
                                                <b-col cols="12">
                                                    <span>Email: <b>{{ user.email }}</b></span>
                                                </b-col>
                                                <b-col cols="12">
                                                    <span>Role: <b>{{ user.role.role }}</b> </span>
                                                </b-col>
                                            </b-row>
                                        </b-col>
                                        <b-col cols="6">
                                            <b-row>
                                                <b-col cols="12">
                                                    <span>Color scheme count: <b>{{ user.color_scheme_count }}</b> </span>
                                                </b-col>
                                                <b-col cols="12">
                                                    <span>Template count: <b>{{ user.template_count }}</b> </span>
                                                </b-col>
                                                <b-col cols="12">
                                                    <span>Banner count: <b>{{ user.banner_count }}</b> </span>
                                                </b-col>
                                            </b-row>
                                        </b-col>
                                    </b-row>
                                </b-col>
                                <!-- Button to show all info -->
                                <b-col cols="4">
                                    <b-row class="h-100">
                                        <b-col class="d-flex">
                                            <router-link 
                                                :to="{ name: 'admin-show-user', params: { uuid: user.uuid } }"
                                                class="w-100 btn btn-theme-blue-light text-white my-auto"
                                            >Show all info</router-link>
                                        </b-col>
                                    </b-row>
                                </b-col>
                            </b-row>
                        </b-col>
                    </b-row>
                    <b-row class="pt-5">
                        <b-col offset="5" cols="1" v-if="prev_page != 0">
                            <router-link :to="{ name: 'admin-all-users', params: { page: prev_page } }">Prev page</router-link>
                        </b-col>
                        <b-col offset="5" cols="1" v-else>
                        </b-col>
                        <b-col cols="1" v-if="next_page_enabled">
                            <router-link :to="{ name: 'admin-all-users', params: { page: next_page } }">Next page</router-link>
                        </b-col>
                    </b-row>
                </div>
                <div v-else>
                    <span>
                        No users match filter
                    </span>
                </div>
            </div>
        </div>
        <div v-else>
            <span class="error-text-single">{{ exception }}</span>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    
    export default {
        data() {
            return {
                users: [],
                exception: '',
                loading: true,
                user_filter: {
                    name: '',
                    email: '',
                    role: '',
                    color_scheme_min: '',
                    color_scheme_max: '',
                    template_min: '',
                    template_max: '',
                    banner_min: '',
                    banner_max: '',
                },
                page: 1,
                next_page_enabled: true,
                user_roles: []
            }
        },
        computed: {
            prev_page: function() {
                return Number(this.page) - 1;
            },
            next_page: function() {
                return Number(this.page) + 1;
            },
        },
        watch: {
            '$route.params.page': function() {
                if(typeof this.$route.params.page != 'undefined') {
                    this.page = this.$route.params.page;
                    this.getAllUsers();
                }
            },
            user_filter: {
                handler() {
                    if(this.page != 1) {
                        this.$router.push({ name: 'admin-all-users', params: { page: 1 } });
                    } else {
                        this.getAllUsers();
                    }
                },
                deep: true
            }
        },
        created(){
            this.getUserRoles();
            this.getAllUsers();
        },
        methods: {
            //Get all user roles from database
            async getUserRoles() {
                await axios.get("/admin/user/roles").then((response) => {
                        this.user_roles = response.data.user_roles;
                        this.user_roles.unshift({value: '', text: 'All'});
                    })
                    .catch((error) => {
                        this.user_roles = null;
                    });
            },

            //Get all user users from database
            async getAllUsers(){
                await axios.get("/admin/users", { 
                            params: { 
                                name: this.user_filter.name,
                                email: this.user_filter.email, 
                                role: this.user_filter.role, 
                                color_scheme_min: this.user_filter.color_scheme_min, 
                                color_scheme_max: this.user_filter.color_scheme_max, 
                                template_min: this.user_filter.template_min, 
                                template_max: this.user_filter.template_max, 
                                banner_min: this.user_filter.banner_min, 
                                banner_max: this.user_filter.banner_max, 
                                page: this.page
                            }
                        }
                    ).then((response) => {
                        this.users = response.data.users;
                        this.next_page_enabled = response.data.next_page;
                        this.loading = false;
                    })
                    .catch((error) => {
                        this.exception = error.response.data.message;
                        this.loading = false;
                    });
            },
        }
    }

</script>