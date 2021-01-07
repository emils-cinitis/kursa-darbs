<template>
    <div>
        <div v-if="exception == ''">
            <div v-if="banners.length > 0">
                <b-row class="filter-row mx-auto">
                    <!-- Time filter -->
                    <b-col cols="6">
                        <b-form-group
                            label="Created:"
                            label-for="created"
                        >
                            <b-form-select 
                                id="created"
                                v-model="banner_filter.created" 
                                :options="created_options">
                            </b-form-select>
                        </b-form-group>
                    </b-col>

                    <!-- Order filter -->
                    <b-col cols="6">
                        <b-form-group
                            label="Order:"
                            label-for="order"
                        >
                            <b-form-select 
                                id="order"
                                v-model="banner_filter.order" 
                                :options="order_options">
                            </b-form-select>
                        </b-form-group>
                    </b-col>
                </b-row>

                <!-- Show all banners -->
                <div class="table-container mx-auto" v-if="banners.length > 0">
                    <b-row>
                        <b-col cols="4" v-for="(banner, key) in banners" :key="key">
                            <preview :banner="banner" />
                            <b-row>
                                <b-col cols="12">
                                    <router-link 
                                        :to="{ name: 'public-banner', params: { uuid: banner.uuid } }"
                                        class="w-100 btn btn-theme-blue-light text-white my-auto"
                                    >Show banner</router-link>
                                </b-col>
                            </b-row>
                        </b-col>
                    </b-row>
                    <b-row class="pt-5">
                        <b-col offset="5" cols="1" v-if="prev_page != 0">
                            <router-link :to="{ name: 'public-banners', params: { page: prev_page } }">Prev page</router-link>
                        </b-col>
                        <b-col offset="5" cols="1" v-else>
                        </b-col>
                        <b-col cols="1">
                            <router-link :to="{ name: 'public-banners', params: { page: next_page } }">Next page</router-link>
                        </b-col>
                    </b-row>
                </div>
                <div v-else>
                    <span>
                        No banners match filter
                    </span>
                </div>
            </div>
            <div v-else>
                <span v-if="loading">Loading...</span>
                <span v-else>
                    No banners loaded <router-link :to="{ name: 'public-banners', params: { page: prev_page } }">Prev page</router-link>
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
    import Preview from '../Banners/Preview.vue';
    
    export default {
        data() {
            return {
                banners: [],
                exception: '',
                loading: true,
                banner_filter: {
                    created: 0,
                    order: 'DESC'
                },
                created_options: [
                    {
                        value: 7,
                        text: 'This week'
                    },
                    {
                        value: 31,
                        text: 'This month'
                    },
                    {
                        value: 186,
                        text: 'Last 6 months'
                    },
                    {
                        value: 366,
                        text: 'Last year'
                    },
                    {
                        value: 0,
                        text: 'All time'
                    }
                ],
                order_options: [
                    {
                        value: 'DESC',
                        text: 'Newest first'
                    },
                    {
                        value: 'ASC',
                        text: 'Oldest first'
                    }
                ]
            }
        },
        components: { Preview },
        computed: {
            prev_page: function() {
                if(typeof this.$route.params.page !== 'undefined') {
                    return Number(this.$route.params.page) - 1;
                } else {
                    return 0;
                }
            },
            next_page: function() {
                if(typeof this.$route.params.page !== 'undefined') {
                    return Number(this.$route.params.page) + 1;
                } else {
                    return 2;
                }
            },
        },
        watch: {
            banner_filter: {
                handler() {
                    if(typeof this.$route.params.page == "undefined" || this.$route.params.page != 1) {
                        this.$router.push({ name: 'public-banners', params: { page: 1 } });
                    } else {
                        this.getAllBanners();
                    }
                },
                deep: true
            },
            '$route.params.page': {
                handler() {
                    this.getAllBanners();
                }
            }
        },
        created(){
            this.getAllBanners();
        },
        methods: {
            //Get all user banners from database
            async getAllBanners(){
                let page = (typeof this.$route.params.page != "undefined") ? this.$route.params.page : 1;
                await axios.get("/banners", 
                    { params: 
                        { 
                            page: page, 
                            created_at: this.banner_filter.created, 
                            order: this.banner_filter.order 
                        } 
                    }
                    ).then((response) => {
                        this.banners = response.data.banners;
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

<style scoped>
    .filter-row {
        max-width: 50%;
    }
</style>