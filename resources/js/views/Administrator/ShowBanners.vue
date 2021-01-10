<template>
    <div class="block-with-sidebar">
        <div v-if="exception == ''">
            <h1>All Banners</h1>
            <!-- Filters -->
            <b-row>
                <!-- Title filter -->
                <b-col cols="6">
                    <b-form-group
                        label="Banner title:"
                        label-for="title"
                    >
                        <b-form-input 
                            id="title"
                            type="text"
                            v-model="banner_filter.title"
                        />
                    </b-form-group>
                </b-col>

                <!-- User UUID -->
                <b-col cols="6">
                    <b-form-group
                        label="User UUID:"
                        label-for="uuid"
                    >
                        <b-form-input 
                            id="email"
                            type="text"
                            v-model="banner_filter.uuid"
                        />
                    </b-form-group>
                </b-col>
            </b-row>
            <div v-if="banners.length > 0">
                <!-- Table -->
                <div class="table-container mx-auto">
                    <b-row v-for="(banner, key) in banners" :key="key" class="table-row">
                        <b-col cols="12">
                            <b-row>
                                <b-col cols="8">
                                    <b-row>
                                        <b-col cols="6">
                                            <b-row>
                                                <b-col cols="12">
                                                    <span>Title: <b>{{ banner.name }}</b></span>
                                                </b-col>
                                                <b-col cols="12">
                                                    <span>Created by: <b>{{ banner.user.username }}</b></span>
                                                </b-col>
                                            </b-row>
                                        </b-col>
                                        <b-col cols="6">
                                            <b-row>
                                                <b-col cols="12">
                                                    <span>Color scheme: <b>{{ banner.color_scheme.title }}</b> </span>
                                                </b-col>
                                                <b-col cols="12">
                                                    <span>Template: 
                                                        <b>
                                                            <span v-for="(type, key1) in banner.banner_types" :key="key1">
                                                                {{ type }}<span v-if="key1 != banner.banner_types.length - 1">,</span>
                                                            </span>
                                                        </b> 
                                                    </span>
                                                </b-col>
                                            </b-row>
                                        </b-col>
                                    </b-row>
                                </b-col>

                                <!-- Buttons -->
                                <b-col cols="4">
                                    <b-row class="h-100">
                                        <b-col>
                                            <router-link 
                                                :to="{ name: 'admin-show-banner', params: { uuid: banner.uuid } }"
                                                class="w-100 btn btn-theme-blue-light text-white my-auto"
                                            >Show all info</router-link>
                                        </b-col>
                                        <b-col>
                                            <router-link 
                                                :to="{ name: 'admin-show-user', params: { uuid: banner.user_uuid } }"
                                                class="w-100 btn btn-theme-blue-light text-white my-auto"
                                            >Show user</router-link>
                                        </b-col>
                                    </b-row>
                                </b-col>
                            </b-row>
                        </b-col>
                    </b-row>
                    <b-row class="pt-5">
                        <b-col offset="5" cols="1" v-if="prev_page != 0">
                            <router-link :to="{ name: 'admin-all-banners', params: { page: prev_page } }">Prev page</router-link>
                        </b-col>
                        <b-col offset="5" cols="1" v-else>
                        </b-col>
                        <b-col cols="1" v-if="has_next_page">
                            <router-link :to="{ name: 'admin-all-banners', params: { page: next_page } }">Next page</router-link>
                        </b-col>
                    </b-row>
                </div>
            </div>
            <div v-else>
                <span v-if="loading">Loading...</span>
                <span v-else>
                    No banners loaded
                </span>
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
                banners: [],
                banner_filter: {
                    title: '',
                    uuid: ''
                },
                page: 1,
                has_next_page: true,
                exception: '',
                loading: true,
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
                    this.getAllBanners();
                }
            },
            '$route.params.user_uuid': function() {
                if(typeof this.$route.params.user_uuid != 'undefined') {
                    this.banner_filter.uuid = this.$route.params.user_uuid;
                }
            },
            banner_filter: {
                handler() {
                    if(this.page != 1) {
                        this.$router.push({ name: 'admin-all-banners', params: { page: 1 } });
                    } else {
                        this.getAllBanners();
                    }
                },
                deep: true
            }
        },
        created(){
            //Set route params on load
            if(typeof this.$route.params.page != 'undefined') {
                this.page = this.$route.params.page;
            }
            if(typeof this.$route.params.user_uuid != 'undefined') {
                this.banner_filter.uuid = this.$route.params.user_uuid;
            }
            this.getAllBanners();
        },
        methods: {
            //Get all user users from database
            async getAllBanners(){
                await axios.get("/admin/banners", { 
                            params: { 
                                title: this.banner_filter.title,
                                user_uuid: this.banner_filter.uuid,
                                page: this.page
                            }
                        }
                    ).then((response) => {
                        this.banners = response.data.banners;
                        this.has_next_page = response.data.next_page;
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