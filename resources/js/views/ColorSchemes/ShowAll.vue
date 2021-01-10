<template>
    <div class="block-with-sidebar">
        <div v-if="exception == ''">
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


                <delete-error-popup
                    :deletable_item="'Color Scheme'"
                    :banners="delete_color_scheme_banners"
                    @close_popup="delete_color_scheme_banners = [];"
                />
                <delete-popup 
                    :deletable_item="'Color Scheme'" 
                    @delete_item="deleteColorScheme"
                    @close_delete="delete_color_scheme = {};"
                />
            </b-row>
            <div v-else>
                <span v-if="loading">Loading...</span>
                <span v-else>No color schemes, create one <router-link :to="{name: 'add-color-scheme'}">here</router-link></span>
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
    import DeleteErrorPopup from '../../components/DeleteError.vue';
    
    export default {
        data() {
            return {
                color_schemes: [],
                delete_color_scheme_banners: [],
                delete_color_scheme: {},
                error: '',
                exception: '',
                loading: true,
            }
        },
        components: { Preview, DeletePopup, DeleteErrorPopup },
        created(){
            this.getAllColorSchemes();
        },
        methods: {
            //Get all color schemes from database
            async getAllColorSchemes(){
                await axios.get("/user/color-schemes")
                    .then((response) => {
                        //Show color schemes to user
                        this.color_schemes = response.data.color_schemes;
                        this.loading = false;
                    })
                    .catch((error) => {
                        //Show error to user
                        this.exception = error.response.data.message;
                        this.loading = false;
                    });
            },

            //Delete axios for set color scheme
            deleteColorScheme() {
                //Setup color scheme id variable as other variable will be unset because of popup closing
                var deletable_color_scheme_id = this.delete_color_scheme.id;
                axios.delete("/user/color-scheme", { params: { id: deletable_color_scheme_id } } )
                    .then((response) => {
                        //Remove color scheme from array
                        this.color_schemes = this.color_schemes.filter( function(color_scheme) {
                            return color_scheme.id !== deletable_color_scheme_id;
                        });

                        //Show success
                        this.$bvToast.toast(response.data.message, {
                            title: 'Success',
                            variant: 'theme-blue-default',
                            solid: true,
                            appendToast: true,
                            autoHideDelay: 10000
                        });
                    })
                    .catch((error) => {
                        //Show error
                        this.$bvToast.toast(error.response.data.message, {
                            title: 'Error',
                            variant: 'theme-blue-light',
                            solid: true,
                            appendToast: true,
                            autoHideDelay: 10000
                        });
                    });
            },

            //Create preivew for color scheme
            setPreview(key, color_scheme) {
                color_scheme.preview = true;
                this.$set(this.color_schemes, key, color_scheme);
            },

            //Show delete popup or error popup
            deletePopup(color_scheme) {
                //If color scheme has banners, show popup with banner links
                if(color_scheme.banners.length > 0) {
                    this.delete_color_scheme_banners = color_scheme.banners;
                    this.$bvModal.show('deleteError');
                } else {
                    //If color scheme has no banners, show popup to confirm deletion
                    this.delete_color_scheme = color_scheme;
                    this.$bvModal.show('deleteModal');
                }
            },
        }
    }

</script>