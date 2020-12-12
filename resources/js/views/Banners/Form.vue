<template>
    <div class="block-with-sidebar">
        <b-form @submit="saveBanner">
            <!-- Banner name -->
            <b-form-group
                label="Name:"
                label-for="banner-name"
            >
                <b-form-input 
                    id="banner-name"
                    v-model="banner.name" 
                    placeholder="Enter banner name" 
                    type="text"
                    required
                ></b-form-input>
            </b-form-group>
            <b-row v-if='errors.name' class='error-message'>
                <b-col cols="12">
                    <p>{{ errors.name[0] }}</p>
                </b-col>
            </b-row>


            <!-- Banner color scheme -->
            <b-form-group
                label="Banner color scheme:"
                label-for="banner-color-scheme"
            >
                <b-form-select 
                    id="banner-color-scheme"
                    v-model="banner.color_scheme_id" 
                    :options="color_schemes">
                </b-form-select>
            </b-form-group>
            <b-row v-if='errors.color_scheme_id' class='error-message'>
                <b-col cols="12">
                    <p>{{ errors.color_scheme_id[0] }}</p>
                </b-col>
            </b-row>

            <!-- Banner template -->
            <b-form-group
                label="Banner template:"
                label-for="banner-template"
            >
                <b-form-select 
                    id="banner-template"
                    v-model="banner.template_id" 
                    :options="templates">
                </b-form-select>
            </b-form-group>
            <b-row v-if='errors.template_id' class='error-message'>
                <b-col cols="12">
                    <p>{{ errors.template_id[0] }}</p>
                </b-col>
            </b-row>

            <!-- Banner main text -->
            <b-form-group
                label="Banner text:"
                label-for="banner-text"
                v-if="typeof enabled.main_text !== 'undefined' && enabled.main_text"
            >
                <b-form-input 
                    id="banner-text"
                    v-model="banner.main_text"
                    placeholder="Enter banner text" 
                    type="text"
                ></b-form-input>
            </b-form-group>
            <b-row v-if="errors.main_text && typeof enabled.main_text !== 'undefined' && enabled.main_text" class='error-message'>
                <b-col cols="12">
                    <p>{{ errors.main_text[0] }}</p>
                </b-col>
            </b-row>

            <!-- Banner main text -->
            <b-form-group
                label="Banner subtext:"
                label-for="banner-subtext"
                v-if="typeof enabled.sub_text !== 'undefined' && enabled.sub_text"
            >
                <b-form-input 
                    id="banner-subtext"
                    v-model="banner.sub_text"
                    placeholder="Enter banner subtext" 
                    type="text"
                ></b-form-input>
            </b-form-group>
            <b-row v-if="errors.sub_text && typeof enabled.sub_text !== 'undefined' && enabled.sub_text" class='error-message'>
                <b-col cols="12">
                    <p>{{ errors.sub_text[0] }}</p>
                </b-col>
            </b-row>

            <!-- Banner main text -->
            <b-form-group
                label="Banner CTA text:"
                label-for="banner-cta-text"
                v-if="typeof enabled.call_to_action !== 'undefined' && enabled.call_to_action"
            >
                <b-form-input 
                    id="banner-cta-text"
                    v-model="banner.call_to_action"
                    placeholder="Enter banner CTA text" 
                    type="text"
                ></b-form-input>
            </b-form-group>
            <b-row v-if="errors.call_to_action && typeof enabled.call_to_action !== 'undefined' && enabled.call_to_action" class='error-message'>
                <b-col cols="12">
                    <p>{{ errors.call_to_action[0] }}</p>
                </b-col>
            </b-row>

            <!-- ToDo: IMAGE -->
            <div v-if="typeof enabled.image !== 'undefined' && enabled.image">
            IMAGE
            </div>

            <b-row>
                <b-col cols="12">
                    <b-button type="submit" variant="success">Save</b-button>
                </b-col>
            </b-row>

            <b-row>
                <preview :banner='banner_computed' :key='preview_id'/>
            </b-row>
        </b-form>
    </div>
</template>
<script>
    import axios from 'axios';
    import Preview from './Preview';

    export default {
        data() {
            return {
                banner: {
                    name: '',
                    main_text: '',
                    sub_text: '',
                    call_to_action: '',
                    color_scheme_id: 0,
                    template_id: 0,
                },
                enabled: {
                    main_text: false,
                    sub_text: false,
                    call_to_action: false,
                    image: false
                },
                color_schemes: [],
                templates: [],
                blocks: {},
                preview_id: 0,
                errors: {
                    name: '',
                    main_text: '',
                    sub_text: '',
                    cta_text: '',
                    color_scheme_id: '',
                    template_id: ''
                }
            }
        },
        components: { Preview },
        watch: {
            'banner.template_id': function(){
                this.getTemplateBlocks();
            }
        },
        computed: {
            banner_computed: function() {
                let color_scheme = this.getColorSchemeInfoById(this.banner.color_scheme_id);

                let newObject = {};
                Object.assign(newObject, this.banner);
                Object.assign(newObject, { banner_types: this.blocks, color_scheme: color_scheme });
                this.preview_id++;
                return newObject;
            }
        },
        created(){
            let uuid = this.$route.params.uuid;
            this.getUserColorSchemes();
            this.getUserTemplates();
            if(typeof uuid != 'undefined') {
                this.getBannerInfo(uuid);
            }
        },
        methods: {
            getColorSchemeInfoById(id) {
                let color_scheme_return = {};
                this.color_schemes.forEach(color_scheme => {
                    if(color_scheme.value == id) {
                        color_scheme_return = color_scheme;
                    }
                });
                return color_scheme_return;
            },

            //Save banner to DB
            async saveBanner(event) {
                event.preventDefault();
                //ToDo: validate inputs
                await axios.post("/user/banners", this.banner)
                    .then((response) => {
                        // Show success
                    })
                    .catch((error) => {
                        this.errors = error.response.data.messages;
                    });
            },

            //Get existing banner info
            async getBannerInfo(uuid) {
                await axios.get("/user/banner", { params: { uuid: uuid } } )
                    .then((response) => {
                        this.banner = response.data.banner
                    });
                    //Show error aswell ToDo
            },

            //Get usable color schemes
            getUserColorSchemes() {
                axios.get("/user/color-schemes", { params: { input: true } } )
                    .then((response) => {
                        this.color_schemes = response.data.color_schemes
                    });
                    //Show error aswell ToDo
            },

            //Get usable templates
            getUserTemplates() {
                axios.get("/user/templates", { params: { input: true } } )
                    .then((response) => {
                        this.templates = response.data.templates
                    });
                    //Show error aswell ToDo
            },

            //Get used blocks in template
            getTemplateBlocks() {
                axios.get("/user/template/info", { params: { id: this.banner.template_id } } )
                    .then((response) => {
                        this.enabled = response.data.enabled;
                        this.blocks = response.data.position;
                    });
                    //Show error aswell
            },
        }
    }

</script>