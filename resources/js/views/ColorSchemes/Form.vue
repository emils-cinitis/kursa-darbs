<template>
    <div class="block-with-sidebar">
        <div v-if="exception == ''">
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
            <b-modal id="warning" hide-footer title="Color Scheme in use" no-close-on-backdrop>
                <h3>This color scheme is already in use!</h3>
                <p>By modifying this color scheme, all existing banners will change their original colors to the new ones</p>
                <p>Used in these banners: </p>
                <ul>
                    <li v-for='(banner, key) in color_scheme.banners' :key='key'>
                        <router-link :to="{name: 'edit-banner', params: { uuid: banner.uuid } }">{{banner.name}}</router-link>
                    </li>
                </ul>
                <b-button 
                    class="mt-3 text-white" 
                    block 
                    variant="theme-blue-dark" 
                    @click="$bvModal.hide('warning');"
                >Cancel</b-button>

                <b-button 
                    class="mt-3" 
                    variant="danger" 
                    block 
                    @click="storeColorSchemeAxios"
                >Save</b-button>
            </b-modal>
            <b-form id="color-scheme-form" @submit="saveColorScheme" class="form-medium mx-auto">
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
        <div v-else>
            <span class="error-text-single">{{ exception }}</span>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import { Chrome } from 'vue-color';
    import Preview from './Preview';

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
                exception: ''
            }
        },
        components: {
            Chrome,
            Preview
        },
        created(){
            let id = this.$route.params.id;
            
            if(typeof id != 'undefined') {
                this.getColorSchemeInfo(id);
            }
        },
        methods: {
            saveColorScheme(event) {
                event.preventDefault();
                
                if(this.color_scheme.banners && this.color_scheme.banners.length > 0) {
                    this.$bvModal.show('warning');
                } else {
                    this.storeColorSchemeAxios();
                }
            },
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
            async getColorSchemeInfo(id) {
                await axios.get("/user/color-scheme", { params: { id: id } } )
                    .then((response) => {
                        this.color_scheme = response.data.color_scheme
                    }).catch((error) => {
                        this.exception = error.response.data.message;
                    });
            },
            showColorPicker(name) {
                this.opened_color_selector = name;
                this.selected_color =  this.color_scheme[name];
            
                this.$bvModal.show('color-picker');
            },
            hideColorPicker() {
                this.opened_color_selector = '';
                this.selected_color = {};

                this.$bvModal.hide('color-picker');
            },
            saveColor() {
                this.color_scheme[this.opened_color_selector] = this.selected_color.hex8;
                this.hideColorPicker();
            },
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