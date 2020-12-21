<template>
    <div class="block-with-sidebar">
        <h1>All color schemes</h1>
        <b-row v-if="color_schemes.length > 0">
            <b-col v-if="error" cols="12">
                <span class="error-text-single">{{ error }}</span>
            </b-col>
            <b-col class="col-12 col-md-4" v-for="(color_scheme, key) in color_schemes" :key="key">
                <div class="color-scheme-container overflow-hidden text-center">
                    <b-row>
                        <b-col 
                            :class="(typeof color_scheme.preview == 'undefined' || color_scheme.preview == false) 
                            ? 'col-12' : 'col-6'"
                        >
                            <div class="color-scheme-title">
                                {{ color_scheme.title }}
                            </div>
                            <b-row>
                                <b-col class="col-12 px-4">
                                    <router-link 
                                        :to="{name: 'edit-color-scheme', params: { id: color_scheme.id } }" 
                                        class="btn btn-theme-blue-default text-white w-100 my-2"
                                    >Edit</router-link>
                                </b-col>

                                <b-col 
                                    :class="'pl-4 ' + 
                                    ((typeof color_scheme.preview == 'undefined' || color_scheme.preview == false) ? 'col-6 pr-1' : 'col-12 pr-4')"
                                >
                                    <b-button 
                                        @click="deletePopup(color_scheme)" 
                                        variant="danger"
                                        class="w-100 text-white my-2"
                                    >Delete</b-button>
                                </b-col>
                                
                                <b-col 
                                    v-if="typeof color_scheme.preview == 'undefined' || color_scheme.preview == false"
                                    class="col-6 pr-4 pl-1"
                                >
                                    <b-button 
                                        @click="setPreview(key, color_scheme)"
                                        variant="theme-blue-default"
                                        class="w-100 text-white my-2"
                                    >Preview</b-button>
                                </b-col>
                            </b-row>
                        </b-col>
                        <b-col 
                            cols="6"
                            v-if="typeof color_scheme.preview != 'undefined' && color_scheme.preview"
                        >
                            <preview 
                                class="h-100 pt-4"
                                :background_color="color_scheme.background_color"
                                :text_color="color_scheme.text_color"
                                :cta_color="color_scheme.cta_color"
                            />
                        </b-col>
                    </b-row>
                </div>
            </b-col>


            <b-modal id="deleteError" hide-footer title="Color Scheme in use" no-close-on-backdrop>
                <h3>This color scheme is already in use!</h3>
                <p>Cannot delete color scheme whilst it's in use by any banner</p>
                <p>Used in these banners: </p>
                <ul>
                    <li v-for='(banner, key) in delete_color_scheme_banners' :key='key'>
                        <router-link :to="{name: 'edit-banner', params: { uuid: banner.uuid } }">{{ banner.name }}</router-link>
                    </li>
                </ul>
                <b-button 
                    variant="theme-blue-dark" 
                    class="text-white w-100" 
                    @click="closeDeleteError"
                >Close</b-button>
            </b-modal>
            <b-modal id="deleteModal" hide-footer title="Delete Color Scheme" no-close-on-backdrop>
                <h3>Are you sure you want to delete this banner?</h3>
                <p>This cannot be undone!</p>
                <b-button 
                    variant="theme-blue-dark"
                    block
                    class="text-white col" 
                    @click="delete_color_scheme = {}; $bvModal.hide('deleteModal')"
                >Close</b-button>
                <b-button 
                    variant="danger"
                    block
                    class="text-white col" 
                    @click="deleteColorScheme"
                >Delete</b-button>
            </b-modal>
        </b-row>
        <div v-else>
            <span v-if="error == ''">No color schemes</span>
            <span v-else class="error-text-single">{{ error }}</span>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import Preview from './Preview';
    
    export default {
        data() {
            return {
                color_schemes: [],
                delete_color_scheme_banners: [],
                delete_color_scheme: {},
                error: ""
            }
        },
        components: { Preview },
        created(){
            this.getAllColorSchemes();
        },
        methods: {
            async getAllColorSchemes(){
                await axios.get("/user/color-schemes")
                    .then((response) => {
                        this.color_schemes = response.data.color_schemes;
                    })
                    .catch((error) => {
                        this.error = error.response.data.message;
                    });
            },
            deleteColorScheme() {
                axios.delete("/user/color-scheme", { params: { id: this.delete_color_scheme.id } } )
                    .then((response) => {
                        this.getAllColorSchemes();
                        this.delete_color_scheme = {};
                        this.$bvModal.hide('deleteModal');
                    })
                    .catch((error) => {
                        this.error = error.response.data.message;
                        this.$bvModal.hide('deleteModal');
                    });
            },
            setPreview(key, color_scheme) {
                color_scheme.preview = true;
                this.$set(this.color_schemes, key, color_scheme);
            },
            deletePopup(color_scheme) {
                if(color_scheme.banners.length > 0) {
                    this.delete_color_scheme_banners = color_scheme.banners;
                    this.$bvModal.show('deleteError');
                } else {
                    this.delete_color_scheme = color_scheme;
                    this.$bvModal.show('deleteModal');
                }
            },
            closeDeleteError() {
                this.delete_color_scheme = [];
                this.$bvModal.hide('deleteError');
            }
        }
    }

</script>