<template>
    <div class="block-with-sidebar">
        <b-modal id="preview" hide-footer title="Banner preview" size="xl">
            <preview :banner='selected_banner' />
        </b-modal>
        <h1>All banners</h1>
        <div v-if="banners.length > 0" class="banner-page-container">
            <b-row v-for="(banner, key) in banners" :key="key">
                <b-col cols="12">
                    <b-row>
                        <b-col cols="12">
                            <span class="banner-title">Title: {{banner.name}}</span>
                        </b-col>
                        <b-col cols="6">
                            <span>Template types: 
                                <span v-for="(type, key1) in banner.banner_types" :key="key1">
                                    {{ type }}
                                </span>
                            </span>
                        </b-col>
                        <b-col cols="6">
                            <span>Color scheme: {{ banner.color_scheme.title }}</span>
                        </b-col>
                        <b-col cols="6">
                            <span>Unique views: ToDo</span>
                        </b-col>
                        <b-col cols="6">
                            <span>Downloads: ToDo</span>
                        </b-col>
                        <b-col cols="12">
                            <router-link :to="{name: 'edit-banner', params: { uuid: banner.uuid } }" class="btn btn-primary mr-2">Edit</router-link>
                            <router-link :to="{name: 'public-banner', params: { uuid: banner.uuid } }" class="btn btn-secondary mr-2">Open public link</router-link>
                            <b-button @click="setPreview(key)">Preview</b-button>
                            <b-button>Download ToDo</b-button>
                            <b-button>Delete ToDo</b-button>
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
                    });
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