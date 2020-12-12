<template>
    <div class="block-with-sidebar">
        <h1>All templates</h1>
        <b-modal id="preview" hide-footer title="Template preview" size="xl">
            <preview :template='selected_template' />
        </b-modal>
        <div v-if="templates.length > 0">
            <b-row v-for="(template, key) in templates" :key="key">
                <b-col cols="12">
                    <b-row>
                        <b-col cols="12">
                            <span class="template-title">Title : {{template.title}}</span>
                        </b-col>
                        <b-col cols="6">
                            <span>Template types : 
                                <span v-for="(type, key1) in template.banner_types" :key="key1">
                                    {{ type }}
                                </span>
                            </span>
                        </b-col>
                        <b-col cols="6">
                            <span>Banners created : {{ template.banner_count }}</span>
                        </b-col>
                        <b-col cols="12">
                            <router-link :to="{name: 'edit-template', params: { id: template.id } }" class="btn btn-primary mr-2">Edit</router-link>
                            <b-button @click="setPreview(key)" class="mr-2">Preview ToDo</b-button>
                            <b-button>Delete ToDo</b-button>
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
        </div>
        <div v-else>
            <span>No templates</span>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import Preview from './Preview';
    
    export default {
        data() {
            return {
                templates: [],
                selected_template: {}
            }
        },
        components: { Preview },
        created(){
            this.getAllTemplates();
        },
        methods: {
            async getAllTemplates(){
                await axios.get("/user/templates")
                    .then((response) => {
                        this.templates = response.data.templates;
                    });
            },
            getTemplatePreivew(template_id) {
                axios.get("/user/template/info", { params: { id: template_id } } )
                    .then((response) => {
                        this.enabled = response.data.enabled;
                        this.blocks = response.data.position;
                    });
                    //Show error aswell
            },
            setPreview(key) {
                if(typeof this.templates[key] != 'undefined') {
                    axios.get("/user/template/info", { params: { id: this.templates[key].id } } )
                    .then((response) => {
                        // this.enabled = response.data.enabled;
                        // this.blocks = response.data.position;
                        console.log(response.data)
                        this.selected_template = response.data.position;
                        this.$bvModal.show('preview');
                    });
                }
            }
        }
    }

</script>