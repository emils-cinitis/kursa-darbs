<template>
    <div class="block-with-sidebar">
        <div v-if="exception == ''">
            <h1>All templates</h1>
            <div v-if="templates.length > 0" class="table-container mx-auto">
                <!-- Template row -->
                <b-row v-for="(template, key) in templates" :key="key" class="table-row">
                    <b-col cols="12">
                        <b-row>
                            <b-col cols="12" lg="7">
                                <b-row>
                                    <b-col cols="4">
                                        <span class="template-title">Title : <b>{{template.title}}</b></span>
                                    </b-col>
                                    <b-col cols="4">
                                        <span>Template types : 
                                            <span v-for="(type, key1) in template.banner_types" :key="key1">
                                                <b>{{ type }}</b><span v-if="key1 != template.banner_types.length - 1">, </span>
                                            </span>
                                        </span>
                                    </b-col>
                                    <b-col cols="4">
                                        <span>Banners created : <b>{{ template.banners.length }}</b></span>
                                    </b-col>
                                </b-row>
                            </b-col>
                            <b-col cols="12" lg="5">
                                <b-row class="h-100">
                                    <b-col cols="12" lg="6" xl="4" class="mb-1 mb-xl-auto my-xl-auto">
                                        <router-link 
                                            :to="{name: 'edit-template', params: { id: template.id } }" 
                                            class="w-100 btn btn-primary mr-2"
                                        >Edit</router-link>
                                    </b-col>
                                    <b-col cols="12" lg="6" xl="4" class="mb-1 mb-xl-auto my-xl-auto">
                                        <b-button 
                                            @click="setPreview(key)" 
                                            class="mr-2 w-100"
                                        >Preview</b-button>
                                    </b-col>
                                    <b-col cols="12" lg="6" xl="4" class="mb-1 mb-xl-auto my-xl-auto">
                                        <b-button 
                                            variant="danger" 
                                            @click="deletePopup(template)" 
                                            class="w-100"
                                        >Delete</b-button>
                                    </b-col>
                                </b-row>
                            </b-col>
                        </b-row>
                    </b-col>
                </b-row>

                <!-- Preview modal -->
                <b-modal id="preview" hide-footer title="Template preview" size="xl">
                    <preview :template='selected_template' />
                </b-modal>
                <!-- Delete error modal -->
                <delete-error-popup
                    :deletable_item="'Template'"
                    :banners="delete_template_banners"
                    @close_popup="delete_template_banners = [];"
                />
                <!-- Delete modal -->
                <delete-popup 
                    :deletable_item="'Template'" 
                    @delete_item="deleteTemplate"
                    @close_delete="delete_template = {};"
                />
            </div>
            <div v-else>
                <span v-if="loading">Loading...</span>
                <span v-else>No templates, create one <router-link :to="{name: 'add-template'}">here</router-link></span>
            </div>
        </div>
        <div v-else>
            <span class="error-text-single">{{ exception }}</span>
        </div>
        <b-modal id="error" hide-footer title="Preview error">
            <span class="error-text-single">{{ preview_error }}</span>
        </b-modal>
    </div>
</template>
<script>
    import axios from 'axios';
    import Preview from './Preview';
    import DeletePopup from '../../components/DeletePopup.vue';
    import DeleteErrorPopup from '../../components/DeleteError.vue';
    
    export default {
        data() {
            return {
                templates: [],
                selected_template: {},
                delete_template: {},
                delete_template_banners: [],
                preview_error: '',
                exception: '',
                loading: true,
            }
        },
        components: { Preview, DeletePopup, DeleteErrorPopup },
        created(){
            this.getAllTemplates();
        },
        methods: {
            //Get all user templates from database
            async getAllTemplates(){
                await axios.get("/user/templates")
                    .then((response) => {
                        //Show user templates
                        this.templates = response.data.templates;
                        this.loading = false;
                    })
                    .catch((error) => {
                        //Show error to user
                        this.exception = error.response.data.message;
                        this.loading = false;
                    });
            },

            //Show template preview
            setPreview(key) {
                if(typeof this.templates[key] != 'undefined') {
                    axios.get("/user/template/info", { params: { id: this.templates[key].id } } )
                    .then((response) => {
                        this.selected_template = response.data.position;
                        this.$bvModal.show('preview');
                    })
                    .catch((error) => {
                        this.preview_error = error.response.data.message;
                        this.$bvModal.show('error');
                    });
                }
            },

            //Show delete popup
            deletePopup(template) {
                //If template has banners show all banners that use this template
                if(template.banners.length > 0) {
                    this.delete_template_banners = template.banners;
                    this.$bvModal.show('deleteError');
                } else {
                    //If template has no banners, show popup to confirm deletion
                    this.delete_template = template;
                    this.$bvModal.show('deleteModal');
                }
            },

            //Delete template
            deleteTemplate() {
                //Setup template delete id as other variable will be unset because of popup closing
                var deletable_template_id = this.delete_template.id;
                axios.delete("/user/template", { params: { id: deletable_template_id } } )
                    .then((response) => {
                        //Remove template from array
                        this.templates = this.templates.filter( function(template) {
                            return template.id !== deletable_template_id;
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
            }
        }
    }

</script>