<template>
    <div class="block-with-sidebar">
        <b-modal id="preview" hide-footer title="Banner preview" size="xl">
            <preview :banner='selected_banner' />
        </b-modal>
        <h1>All banners</h1>
        <div v-if="banners.length > 0" class="banner-page-container">
            <b-row v-for="(banner, key) in banners" :key="key" class="banner-page-row">
                <b-col cols="12">
                    <b-row>
                        <b-col cols="6">
                            <b-row>
                                <b-col cols="12">
                                    <span class="banner-title">Title: <b>{{banner.name}}</b></span>
                                </b-col>
                                <b-col cols="6">
                                    <span>Template types: 
                                        <b>
                                            <span v-for="(type, key1) in banner.banner_types" :key="key1">
                                                {{ type }}
                                                <span v-if="key1 != banner.banner_types.length - 1">,</span>
                                            </span>
                                        </b>
                                    </span>
                                </b-col>
                                <b-col cols="6">
                                    <span>Color scheme: <b>{{ banner.color_scheme.title }} </b> </span>
                                </b-col>
                                <b-col cols="6">
                                    <span>Unique views: <b>ToDo</b></span>
                                </b-col>
                                <b-col cols="6">
                                    <span>Downloads: <b>ToDo</b></span>
                                </b-col>
                            </b-row>
                        </b-col>
                        <b-col cols="6">
                            <b-row class="h-100">
                                <b-col cols="3" class="my-auto">
                                    <b-button 
                                        variant="theme-blue-dark" 
                                        class="w-100 text-white" 
                                        @click="setPreview(key)"
                                    >Preview</b-button>
                                </b-col>

                                <b-col cols="3" class="my-auto">
                                    <router-link 
                                        :to="{name: 'public-banner', params: { uuid: banner.uuid } }" 
                                        class="w-100 btn btn-theme-blue-dark text-white"
                                    >Open</router-link>
                                </b-col>

                                <b-col cols="3" class="my-auto">
                                    <b-button 
                                        variant="theme-blue-dark" 
                                        class="w-100 text-white"
                                    >Download</b-button>
                                </b-col>

                                <b-col cols="3" class="my-auto">
                                    <b-dropdown 
                                        text="More"
                                        variant="theme-blue-light text-white"
                                        class="banner-dropdown"
                                    >
                                        <router-link :to="{name: 'edit-banner', params: { uuid: banner.uuid } }" class="w-100 mb-1 btn btn-theme-blue-light text-white">Edit</router-link>
                                        <b-button variant="danger" class="w-100">Delete ToDo</b-button>
                                    </b-dropdown>
                                </b-col>
                            </b-row>
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
        </div>
        <div v-else>
            <span>No banners, create one <a @click="$router.push({name: 'add-banner', params: { uuid: banner.uuid } })">here</a></span>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import Preview from './Preview';
    
    export default {
        data() {
            return {
                banners: [],
                selected_banner: {}
            }
        },
        components: { Preview },
        created(){
            this.getAllBanners();
        },
        methods: {
            async getAllBanners(){
                await axios.get("/user/banners")
                    .then((response) => {
                        this.banners = response.data.banners;
                    }); //ToDo: error
            },
            setPreview(key) {
                if(typeof this.banners[key] != 'undefined') {
                    axios.get("/banner", { params: { uuid: this.banners[key].uuid} } )
                        .then((response) => {
                            this.selected_banner = response.data.banner;
                            this.$bvModal.show('preview');
                        })
                        .catch((error) => {
                            //this.errors = error.response.data.messages;
                            //ToDo show popup with error message
                        });
                }
            }
        }
    }

</script>