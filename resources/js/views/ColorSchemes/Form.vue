<template>
    <div class="block-with-sidebar">
        <b-modal id="color-picker" hide-footer title="Color picker" no-close-on-backdrop @hide="hideColorPicker">
            <chrome v-model="selected_color" />
            <b-button class="mt-3" variant="outline-danger" block @click="hideColorPicker">Cancel</b-button>
            <b-button class="mt-3" variant="success" block @click="saveColor">Save</b-button>
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
            <b-button class="mt-3" block @click="$bvModal.hide('warning');">Cancel</b-button>
            <b-button class="mt-3" variant="outline-danger" block @click="storeColorSchemeAxios">Save</b-button>
        </b-modal>
        <b-form @submit="saveColorScheme">
            <b-form-group
                label="Title:"
                label-for="color-scheme-title"
            >
                <b-form-input 
                    id="color-scheme-title"
                    v-model="color_scheme.title" 
                    placeholder="Enter color scheme title" 
                    type="text"
                ></b-form-input>
            </b-form-group>
            <b-row v-if='errors.title' class='error-message'>
                <b-col cols="12">
                    <p>{{ errors.title[0] }}</p>
                </b-col>
            </b-row>

            <!-- Background color -->
            <b-form-group
                label="Background color:"
                label-for="color-scheme-background-color"
            >
                <b-form-input 
                    id="color-scheme-background-color"
                    v-model="color_scheme.background_color"
                    placeholder="Select background color" 
                    @click="showColorPicker('background_color')"
                    readonly
                    type="text"
                ></b-form-input>
            </b-form-group>
            <b-row v-if='errors.background_color' class='error-message'>
                <b-col cols="12">
                    <p>{{ errors.background_color[0] }}</p>
                </b-col>
            </b-row>

            <!-- Text color -->
            <b-form-group
                label="Text color:"
                label-for="color-scheme-text-color"
            >
                <b-form-input 
                    id="color-scheme-text-color"
                    v-model="color_scheme.text_color"
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

            <!-- CTA color -->
            <b-form-group
                label="CTA color:"
                label-for="color-scheme-cta-color"
            >
                <b-form-input 
                    id="color-scheme-cta-color"
                    v-model="color_scheme.cta_color"
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

            <b-row>
                <preview 
                    class="w-50 mx-auto border"
                    :background_color="color_scheme.background_color"
                    :text_color="color_scheme.text_color"
                    :cta_color="color_scheme.cta_color"
                />
            </b-row>

            <b-row>
                <b-col cols="12">
                    <b-button type="submit" variant="success">Save</b-button>
                </b-col>
            </b-row>
        </b-form>
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
                    cta_color: ''
                }
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
                //ToDo: validate inputs
                 await axios.post("/user/color-scheme", this.color_scheme)
                        .then((response) => {
                            // Show success ToDo:
                        });
                        //Show error aswell ToDo:
            },
            async getColorSchemeInfo(id) {
                await axios.get("/user/color-scheme", { params: { id: id } } )
                    .then((response) => {
                        this.color_scheme = response.data.color_scheme
                    });
                    //Show error aswell ToDo:
            },
            showColorPicker(name) {
                this.opened_color_selector = name;
                this.selected_color = this.colors[name];
                this.$bvModal.show('color-picker');
            },
            hideColorPicker() {
                this.opened_color_selector = '';
                this.selected_color = {};
                this.$bvModal.hide('color-picker');
            },
            saveColor() {
                this.colors[this.opened_color_selector] = this.selected_color;
                this.color_scheme[this.opened_color_selector] = this.selected_color.hex8;
                this.hideColorPicker();
            }
        }
    }

</script>