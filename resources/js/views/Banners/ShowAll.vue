<template>
    <div class="block-with-sidebar">
        <div v-if="exception == ''">
            <h1>All banners</h1>
            <div v-if="banners.length > 0" class="table-container mx-auto">
                <!-- Banner row -->
                <b-row v-for="(banner, key) in banners" :key="key" class="table-row">
                    <b-col cols="12">
                        <b-row>
                            <b-col cols="12" lg="4">
                                <b-row>
                                    <b-col cols="12">
                                        <span class="banner-title">Title: <b>{{banner.name}}</b></span>
                                    </b-col>
                                    <b-col cols="12">
                                        <span>Template types: 
                                            <b>
                                                <span v-for="(type, key1) in banner.banner_types" :key="key1">
                                                    {{ type }}<span v-if="key1 != banner.banner_types.length - 1">,</span>
                                                </span>
                                            </b>
                                        </span>
                                    </b-col>
                                    <b-col cols="12">
                                        <span>Color scheme: <b>{{ banner.color_scheme.title }} </b> </span>
                                    </b-col>
                                </b-row>
                            </b-col>
                            <!-- Buttons -->
                            <b-col cols="12" lg="8">
                                <b-row class="h-100">
                                    <b-col cols="12" lg="6" xl="3" class="mb-1 mb-xl-auto my-xl-auto">
                                        <b-button 
                                            variant="theme-blue-dark" 
                                            class="w-100 text-white" 
                                            @click="setPreview(key)"
                                        >Preview</b-button>
                                    </b-col>

                                    <b-col cols="12" lg="6" xl="3" class="mb-1 mb-xl-auto my-xl-auto">
                                        <router-link 
                                            :to="{name: 'public-banner', params: { uuid: banner.uuid } }" 
                                            class="w-100 btn btn-theme-blue-dark text-white"
                                        >Open</router-link>
                                    </b-col>

                                    <b-col cols="12" lg="6" xl="3" class="mb-1 mb-xl-auto my-xl-auto">
                                        <b-button 
                                            variant="theme-blue-dark" 
                                            class="w-100 text-white"
                                        >Download</b-button>
                                    </b-col>

                                    <b-col cols="12" lg="6" xl="3" class="mb-1 mb-xl-auto my-xl-auto">
                                        <b-dropdown 
                                            text="More"
                                            right
                                            variant="theme-blue-light text-white"
                                            class="banner-dropdown w-100"
                                        >
                                            <router-link :to="{name: 'edit-banner', params: { uuid: banner.uuid } }" class="w-100 mb-1 btn btn-theme-blue-light text-white">Edit</router-link>
                                            <b-button variant="danger" class="w-100" @click="deletePopup(banner)">Delete</b-button>
                                        </b-dropdown>
                                    </b-col>
                                </b-row>
                            </b-col>
                        </b-row>
                    </b-col>
                </b-row>
                <!-- Preview modal -->
                <b-modal id="preview" hide-footer title="Banner preview" size="xl">
                    <preview :banner='selected_banner' />
                </b-modal>

                <!-- Preview error modal -->
                <b-modal id="error" hide-footer title="Preview error">
                    <span class="error-text-single">{{ preview_error }}</span>
                </b-modal>

                <!-- Delete modal -->
                <delete-popup 
                    :deletable_item="'Banner'" 
                    @delete_item="deleteBanner"
                    @close_delete="delete_banner = {};"
                />
            </div>
            <div v-else>
                <span v-if="loading">Loading...</span>
                <span v-else>
                    No banners, create one <router-link :to="{name: 'add-banner'}">here</router-link>
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
    import Preview from './Preview.vue';
    import DeletePopup from '../../components/DeletePopup.vue';
    
    export default {
        data() {
            return {
                banners: [],
                selected_banner: {},
                delete_banner: {},
                exception: '',
                preview_error: '',
                loading: true,
            }
        },
        components: { Preview, DeletePopup },
        created(){
            this.getAllBanners();
        },
        methods: {
            //Get all user banners from database
            async getAllBanners(){
                await axios.get("/user/banners")
                    .then((response) => {
                        this.banners = response.data.banners;
                        this.loading = false;
                    })
                    .catch((error) => {
                        this.exception = error.response.data.message;
                        this.loading = false;
                    });
            },

            //Delete banner from database
            deleteBanner() {
                var deletable_banner_uuid = this.delete_banner.uuid;
                axios.delete("/user/banner", { params: { uuid: deletable_banner_uuid } } )
                    .then((response) => {
                        this.banners = this.banners.filter( function(banner) {
                            return banner.uuid !== deletable_banner_uuid;
                        });
                        this.$bvToast.toast(response.data.message, {
                            title: 'Success',
                            variant: 'theme-blue-default',
                            solid: true,
                            appendToast: true,
                            autoHideDelay: 10000
                        });
                    })
                    .catch((error) => {
                        this.$bvToast.toast(error.response.data.message, {
                            title: 'Error',
                            variant: 'theme-blue-light',
                            solid: true,
                            appendToast: true,
                            autoHideDelay: 10000
                        });
                    });
            },

            //Setup preview information
            setPreview(key) {
                if(typeof this.banners[key] != 'undefined') {
                    this.preview_error = '';
                    axios.get("/banner", { params: { uuid: this.banners[key].uuid} } )
                        .then((response) => {
                            this.selected_banner = response.data.banner;
                            this.$bvModal.show('preview');
                        })
                        .catch((error) => {
                            this.preview_error = error.response.data.message;
                            this.$bvModal.show('error');
                        });
                }
            },

            //Show delete popup
            deletePopup(banner) {
                this.delete_banner = banner;
                this.$bvModal.show('deleteModal');
            }
        }
    }

</script>