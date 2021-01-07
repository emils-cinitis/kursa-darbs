<template>
    <div class="block-with-sidebar">
        <!-- Image cropping -->
        <b-modal id="cropper-modal" hide-footer title="Crop Image" no-close-on-backdrop size="xl" @hide="hideCropperModal">
            <cropper
                class="cropper"
                ref="cropper"
                :src="banner.image"
                :minHeight="min_cropper_height"
                :minWidth="min_cropper_width"
                :stencilProps="cropper_stencil"
            />
            <b-row>
                <b-button 
                    class="col-6 mt-3 text-white" 
                    variant="theme-blue-light" 
                    block 
                    @click="hideCropperModal"
                >Cancel</b-button>

                <b-button 
                    class="col-6 mt-3 text-white" 
                    variant="theme-blue-dark" 
                    block 
                    @click="saveCrop"
            >Crop</b-button>
            </b-row>
                
        </b-modal>

        <!-- Main content -->
        <b-row v-if="exception == ''">
            <!-- Form -->
            <b-form id="banner-form" @submit="saveBanner" class="col-12 col-md-6">
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

                <!-- Banner URL -->
                <b-form-group
                    label="Link:"
                    label-for="banner-link"
                >
                    <b-form-input 
                        id="banner-link"
                        v-model="banner.link_url" 
                        placeholder="Enter banner link" 
                        type="text"
                        required
                    ></b-form-input>
                </b-form-group>
                <b-row v-if='errors.link_url' class='error-message'>
                    <b-col cols="12">
                        <p>{{ errors.link_url[0] }}</p>
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
                        required
                    ></b-form-input>
                </b-form-group>
                <b-row v-if="errors.main_text && typeof enabled.main_text !== 'undefined' && enabled.main_text" class='error-message'>
                    <b-col cols="12">
                        <p>{{ errors.main_text[0] }}</p>
                    </b-col>
                </b-row>

                <!-- Banner subtext text -->
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
                        required
                    ></b-form-input>
                </b-form-group>
                <b-row v-if="errors.sub_text && typeof enabled.sub_text !== 'undefined' && enabled.sub_text" class='error-message'>
                    <b-col cols="12">
                        <p>{{ errors.sub_text[0] }}</p>
                    </b-col>
                </b-row>

                <!-- Banner CTA text -->
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
                        required
                    ></b-form-input>
                </b-form-group>
                <b-row v-if="errors.call_to_action && typeof enabled.call_to_action !== 'undefined' && enabled.call_to_action" class='error-message'>
                    <b-col cols="12">
                        <p>{{ errors.call_to_action[0] }}</p>
                    </b-col>
                </b-row>

                <!-- Banner Image -->
                <b-form-group
                    label="Banner Image:"
                    label-for="banner-image"
                    v-if="typeof enabled.image !== 'undefined' && enabled.image"
                >
                    <b-form-file
                        id="banner-image"
                        v-model="image"
                        placeholder="Choose a file or drop it here..."
                        drop-placeholder="Drop file here..."
                        accept="image/*"
                        @change="loadImageCropper($event)"
                        v-if="banner.image == ''"
                    ></b-form-file>
                    <b-button 
                        v-else
                        variant="danger" 
                        class="text-white w-100" 
                        @click="banner.image = ''"
                    >
                    Delete
                    </b-button>
                </b-form-group>
                <b-row v-if="errors.image && typeof enabled.image !== 'undefined' && enabled.image" class='error-message'>
                    <b-col cols="12">
                        <p>{{ errors.image[0] }}</p>
                    </b-col>
                </b-row>

                <!-- Buttons -->
                <b-row class="mb-2">
                    <b-col :class="(banner.uuid != '') ? 'col-4' : 'col-6'">
                        <b-button 
                            variant="theme-blue-light" 
                            class="text-white w-100" 
                            @click="$router.go(-1)"
                        >
                        Back
                        </b-button>
                    </b-col>
                    <b-col
                        cols="4"
                        v-if="banner.uuid != ''"
                    >
                        <b-button 
                            variant="theme-blue-light" 
                            class="text-white w-100" 
                            @click="getBannerInfo(banner.uuid)"
                        >
                        Reset
                        </b-button>
                    </b-col>
                    <b-col :class="(banner.uuid != '') ? 'col-4' : 'col-6'">
                        <b-button 
                            type="submit" 
                            variant="theme-blue-dark" 
                            class="text-white w-100"
                        >
                        Save Banner
                        </b-button>
                    </b-col>
                </b-row>
            </b-form>

            <!-- Preview -->
            <div class="col-12 col-md-6 mt-md-4">
                <preview 
                    v-if="preview_exception == ''"
                    :banner='banner_computed' 
                    :key='preview_id'
                />
                <div v-else>
                    <span class="error-text-single">{{ preview_exception }}</span>
                </div>
            </div>
        </b-row>
        <!-- Exception error -->
        <div v-else>
            <span class="error-text-single">{{ exception }}</span>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import Preview from './Preview';
    import { Cropper } from 'vue-advanced-cropper';
    import 'vue-advanced-cropper/dist/style.css';

    export default {
        data() {
            return {
                banner: {
                    uuid: '',
                    name: '',
                    main_text: '',
                    sub_text: '',
                    call_to_action: '',
                    image: '',
                    link_url: '',
                    color_scheme_id: 1,
                    template_id: 1,
                },
                enabled: {
                    main_text: false,
                    sub_text: false,
                    call_to_action: false,
                    image: false
                },
                image: null,
                color_schemes: [],
                templates: [],
                blocks: {},
                preview_id: 0,
                errors: {
                    name: '',
                    link_url: '',
                    main_text: '',
                    sub_text: '',
                    cta_text: '',
                    image: '',
                    color_scheme_id: '',
                    template_id: ''
                },
                first_load: true,
                exception: '',
                preview_exception: ''
            }
        },
        components: { Preview, Cropper },
        watch: {
            'banner.template_id': function() {
                this.getTemplateBlocks();
                //Do not reset image when template is loaded first time
                if(!this.first_load) {
                    this.banner.image = '';
                } else {
                    this.first_load = false;
                }
            }
        },
        computed: {
            //Used in preview
            banner_computed: function() {
                let color_scheme = this.getColorSchemeInfoById(this.banner.color_scheme_id);

                let newObject = {};
                Object.assign(newObject, this.banner);
                Object.assign(newObject, { banner_types: this.blocks, color_scheme: color_scheme });
                this.preview_id++;
                return newObject;
            },
            //Minimal image height for current template
            min_cropper_height: function() {
                if(this.enabled.image) {
                    let min_height = 0;
                    if(typeof this.blocks.giga != 'undefined') {
                        this.blocks.giga.blocks.forEach((block) => {
                            if(block.block_type.title == "image") {
                                min_height = block.height;
                            }
                        });
                    }
                    if(typeof this.blocks.tower != 'undefined') {
                        this.blocks.tower.blocks.forEach((block) => {
                            if(block.block_type.title == "image") {
                                if(min_height < block.height) {
                                    min_height = block.height;
                                }
                            }
                        });
                    }
                    return min_height;
                } else {
                    return 0;
                }
            },
            //Minimal image width for current template
            min_cropper_width: function() {
                if(this.enabled.image) {
                    let min_width = 0;
                    if(typeof this.blocks.giga != 'undefined') {
                        this.blocks.giga.blocks.forEach((block) => {
                            if(block.block_type.title == "image") {
                                min_width = block.width;
                            }
                        });
                    }
                    if(typeof this.blocks.tower != 'undefined') {
                        this.blocks.tower.blocks.forEach((block) => {
                            if(block.block_type.title == "image") {
                                if(min_width < block.width) {
                                    min_width = block.width;
                                }
                            }
                        });
                    }
                    return min_width;
                } else {
                    return 0;
                }
            },
            //Image aspect ratio for cropper
            cropper_stencil: function() {
                if(this.enabled.image) {
                    return {
                        aspectRatio: (this.min_cropper_width / this.min_cropper_height)
                    };
                } else {
                    return { };
                }
            }
        },
        created(){
            let uuid = this.$route.params.uuid;
            this.getUserColorSchemes();
            this.getUserTemplates();
            if(typeof uuid != 'undefined') {
                this.getBannerInfo(uuid);
            } else {
                this.first_load = false;
            }
            this.getTemplateBlocks();
        },
        methods: {
            //Get all info about color scheme from ID
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
                if(this.validateInputs()) {
                    await axios.post("/user/banners", this.banner)
                        .then((response) => {
                            if(response.data.uuid) {
                                this.banner.uuid = response.data.uuid;
                            }

                            this.$bvToast.toast(response.data.message, {
                                title: 'Success',
                                variant: 'theme-blue-default',
                                solid: true,
                                appendToast: true,
                                autoHideDelay: 10000
                            });

                            this.errors = {};
                        })
                        .catch((error) => {
                            if(error.response.data.messages) {
                                this.errors = error.response.data.messages;
                            } else {
                                let message = (error.response.data.message) ? error.response.data.message : "Fatal error";

                                this.$bvToast.toast(message, {
                                    title: 'Error',
                                    variant: 'danger',
                                    solid: true,
                                    appendToast: true,
                                    autoHideDelay: 10000
                                });
                            }
                        });
                }
            },

            //Get existing banner info
            async getBannerInfo(uuid) {
                await axios.get("/user/banner", { params: { uuid: uuid } } )
                    .then((response) => {
                        let image = response.data.banner.image;
                        this.banner = response.data.banner;
                        this.banner.image = image;
                    })
                    .catch((error) => {
                        // Exception
                        this.exception = error.response.data.message;
                    });
            },

            //Get usable color schemes
            getUserColorSchemes() {
                axios.get("/user/color-schemes", { params: { input: true } } )
                    .then((response) => {
                        this.color_schemes = response.data.color_schemes
                    })
                    .catch((error) => {
                        //Exception
                        this.exception = error.response.data.message;
                    });
            },

            //Get usable templates
            getUserTemplates() {
                axios.get("/user/templates", { params: { input: true } } )
                    .then((response) => {
                        this.templates = response.data.templates
                    })
                    .catch((error) => {
                        //Exception
                        this.exception = error.response.data.message;
                    });
            },

            //Get used blocks in template
            getTemplateBlocks() {
                axios.get("/user/template/info", { params: { id: this.banner.template_id } } )
                    .then((response) => {
                        this.enabled = response.data.enabled;
                        this.blocks = response.data.position;
                    })
                    .catch((error) => {
                        //Preview exception
                        this.preview_exception = error.response.data.message;
                    });
            },

            //Load cropper image and modal
            loadImageCropper(event) {
                //Find uploaded file
                var input = event.target;
                if (input.files && input.files[0]) {
                    //Read image as base64
                    var reader = new FileReader();
                    reader.onload = (e) => {
                        this.banner.image = e.target.result;
                        this.$bvModal.show('cropper-modal');
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            },

            //Hide cropper modal and unset values
            hideCropperModal() {
                this.$bvModal.hide('cropper-modal');
                this.banner.image = '';
                this.image = null;
            },

            //Save crop information to banner object
            saveCrop() {
                this.$bvModal.hide('cropper-modal');
                let { canvas } = this.$refs.cropper.getResult();
                this.banner.image = canvas.toDataURL('image/jpeg', 1.0);
            },
            
            //Validate default inputs
            validateInputs() {
                let inputs = document.querySelectorAll("input");
                var correct = true;
                inputs.forEach((input) => {
                    if(!input.checkValidity()) {
                        input.reportValidity();
                        correct = false;
                    }
                });
                return correct;
            }
        }
    }

</script>