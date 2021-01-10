<template>
    <div class="block-with-sidebar">
        <div v-if="exception == '' && !loading">
            <!-- Color picker modal -->
            <b-modal id="color-picker" hide-footer title="Color picker" no-close-on-backdrop @hide="hideColorPicker">
                <chrome v-model="selected_color" />
                <b-button 
                    class="mt-3 text-white" 
                    variant="theme-blue-light" 
                    block 
                    @click="hideColorPicker"
                >Cancel</b-button>

                <b-button 
                    class="mt-3 text-white" 
                    variant="theme-blue-dark" 
                    block 
                    @click="saveColor"
                >Save</b-button>
            </b-modal>

            <!-- In use warning modal -->
            <in-use-warning 
                :model="'Color scheme'"
                :banners="color_scheme.banners"
                @edit_item="storeColorSchemeAxios"
            />

            <!-- Form inputs -->
            <b-form id="color-scheme-form" @submit="saveColorScheme" class="form-medium mx-auto">
                <!-- Title -->
                <b-form-group
                    label="Title:"
                    label-for="color-scheme-title"
                >
                    <b-form-input 
                        id="color-scheme-title"
                        class="input-default"
                        v-model="color_scheme.title" 
                        placeholder="Enter color scheme title" 
                        minlength="5"
                        maxlength="255"
                        type="text"
                    ></b-form-input>
                </b-form-group>
                <b-row v-if='errors.title' class='error-message'>
                    <b-col cols="12">
                        <p>{{ errors.title[0] }}</p>
                    </b-col>
                </b-row>

                <b-row>
                    <!-- Background color -->
                    <b-col cols="4">
                        <b-form-group
                            label="Background color:"
                            label-for="color-scheme-background-color"
                        >
                            <b-form-input 
                                id="color-scheme-background-color"
                                class="input-color-picker"
                                v-model="color_scheme.background_color"
                                placeholder="Select background color" 
                                @click="showColorPicker('background_color')"
                                pattern="#[0-9a-fA-F]{8}"
                                readonly
                                type="text"
                            ></b-form-input>
                        </b-form-group>
                        <b-row v-if='errors.background_color' class='error-message'>
                            <b-col cols="12">
                                <p>{{ errors.background_color[0] }}</p>
                            </b-col>
                        </b-row>
                    </b-col>

                    <!-- Text color -->
                    <b-col cols="4">
                        <b-form-group
                            label="Text color:"
                            label-for="color-scheme-text-color"
                        >
                            <b-form-input 
                                id="color-scheme-text-color"
                                class="input-color-picker"
                                v-model="color_scheme.text_color"
                                pattern="#[0-9a-fA-F]{8}"
                                placeholder="Select text color"
                                @click="showColorPicker('text_color')"
                                readonly
                                type="text"
                            ></b-form-input>
                        </b-form-group>
                        <b-row v-if='errors.text_color' class='error-message'>
                            <b-col cols="12">
                                <p>{{ errors.text_color[0] }}</p>
                            </b-col>
                        </b-row>
                    </b-col>

                    <!-- CTA color -->
                    <b-col cols="4">
                        <b-form-group
                            label="CTA color:"
                            label-for="color-scheme-cta-color"
                        >
                            <b-form-input 
                                id="color-scheme-cta-color"
                                class="input-color-picker"
                                v-model="color_scheme.cta_color"
                                pattern="#[0-9a-fA-F]{8}"
                                placeholder="Select CTA color"
                                @click="showColorPicker('cta_color')"
                                readonly
                                type="text"
                            ></b-form-input>
                        </b-form-group>
                        <b-row v-if='errors.cta_color' class='error-message'>
                            <b-col cols="12">
                                <p>{{ errors.cta_color[0] }}</p>
                            </b-col>
                        </b-row>
                    </b-col>
                </b-row>

                <!-- Preivew block -->
                <b-row class="pb-2">
                    <b-col cols="12">
                        <preview 
                            class="w-100 border"
                            :background_color="color_scheme.background_color"
                            :text_color="color_scheme.text_color"
                            :cta_color="color_scheme.cta_color"
                        />
                    </b-col>
                </b-row>

                <!-- Buttons -->
                <b-row>
                    <b-col :class="(color_scheme.id != 0) ? 'col-4' : 'col-6'">
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
                        v-if="color_scheme.id != 0"
                    >
                        <b-button 
                            variant="theme-blue-light" 
                            class="text-white w-100" 
                            @click="getColorSchemeInfo(color_scheme.id)"
                        >
                        Reset
                        </b-button>
                    </b-col>
                    <b-col :class="(color_scheme.id != 0) ? 'col-4' : 'col-6'">
                        <b-button 
                            type="submit" 
                            variant="theme-blue-dark" 
                            class="text-white w-100"
                        >
                        Save Color scheme
                        </b-button>
                    </b-col>
                </b-row>
                <b-row v-if='errors.unexpected' class='error-message'>
                    <b-col cols="12">
                        <p>{{errors.unexpected}}</p>
                    </b-col>
                </b-row>
            </b-form>
        </div>
        <div v-else class="w-100">
            <span v-if="loading" class="w-100 d-block text-center">Loading...</span>
            <span v-else class="error-text-single">{{ exception }}</span>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import { Chrome } from 'vue-color';
    import Preview from './Preview.vue';
    import InUseWarning from '../../components/InUsePopup.vue';

    export default {
        data() {
            return {
                color_scheme: {
                    id: 0,
                    title: '',
                    background_color: '#FFFFFFFF',
                    text_color: '#000000FF',
                    cta_color: '#FFFFFFFF'
                },
                selected_color: {},
                colors: {
                    background_color: {},
                    text_color: {},
                    cta_color: {}
                },
                opened_color_selector: '',
                errors: {
                    title: '',
                    background_color: '',
                    text_color: '',
                    cta_color: '',
                    unexpected: ''
                },
                exception: '',
                loading: true
            }
        },
        components: {
            Chrome,
            Preview,
            InUseWarning
        },
        created(){
            let id = this.$route.params.id;
            
            if(typeof id != 'undefined') {
                this.getColorSchemeInfo(id);
            } else {
                this.loading = false;
            }
        },
        methods: {
            //Check if color scheme is in use, if so, show warning
            saveColorScheme(event) {
                event.preventDefault();

                if(this.color_scheme.banners && this.color_scheme.banners.length > 0) {
                    this.$bvModal.show('warning');
                } else {
                    this.storeColorSchemeAxios();
                }
            },

            //Save color scheme to database
            async storeColorSchemeAxios() {
                if(this.validateInputs()) {
                    await axios.post("/user/color-scheme", this.color_scheme)
                        .then((response) => {
                            this.$bvToast.toast(response.data.message, {
                                title: 'Success',
                                variant: 'theme-blue-default',
                                solid: true,
                                appendToast: true,
                                autoHideDelay: 10000
                            });
                            this.color_scheme = response.data.color_scheme;
                        }).catch((error) => {
                            this.errors = error.response.data.messages;
                        });
                }
            },

            //Get saved color scheme information
            async getColorSchemeInfo(id) {
                await axios.get("/user/color-scheme", { params: { id: id } } )
                    .then((response) => {
                        this.color_scheme = response.data.color_scheme;
                        this.loading = false;
                    }).catch((error) => {
                        this.exception = error.response.data.message;
                        this.loading = false;
                    });
            },
            //Show color picker popup
            showColorPicker(name) {
                this.opened_color_selector = name;
                this.selected_color =  this.color_scheme[name];
            
                this.$bvModal.show('color-picker');
            },
            //Hide color picker popup
            hideColorPicker() {
                this.opened_color_selector = '';
                this.selected_color = {};

                this.$bvModal.hide('color-picker');
            },
            //Save color picker input
            saveColor() {
                this.color_scheme[this.opened_color_selector] = this.selected_color.hex8;
                this.hideColorPicker();
            },

            //Validate default inputs
            validateInputs() {
                let inputs = document.querySelectorAll("input");
                var correct = true;
                inputs.forEach((input) => {
                    //Workaround to check if readonly input fields are correct
                    let readOnly = false;
                    if(input.readOnly) {
                        readOnly = true;
                        input.readOnly = false;
                    }

                    if(!input.checkValidity()) {
                        input.reportValidity();
                        if(readOnly) {
                            this.$nextTick(() => {
                                input.readOnly = true;
                            });
                        }
                        correct = false;
                    }

                    //Workaround to check if readonly input fields are correct
                    if(readOnly) {
                        this.$nextTick(() => {
                            input.readOnly = true;
                        });
                    }
                });
                return correct;
            }
        }
    }

</script>