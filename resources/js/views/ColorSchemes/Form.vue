<template>
    <div class="block-with-sidebar">
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
            <!-- ToDo: add color picker -->
            <b-form-group
                label="Color scheme background color:"
                label-for="color-scheme-background-color"
            >
                <b-form-input 
                    id="color-scheme-background-color"
                    v-model="color_scheme.background_color"
                    placeholder="Enter background color" 
                    type="text"
                ></b-form-input>
            </b-form-group>
            <b-form-group
                label="Color scheme CTA color:"
                label-for="color-scheme-cta-color"
            >
                <b-form-input 
                    id="color-scheme-cta-color"
                    v-model="color_scheme.cta_color"
                    placeholder="Enter CTA color" 
                    type="text"
                ></b-form-input>
            </b-form-group>
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
    export default {
        data() {
            return {
                color_scheme: {
                    id: 0,
                    title: '',
                    background_color: '',
                    cta_color: ''
                }
            }
        },
        created(){
            let id = this.$route.params.id;
            
            if(typeof id != 'undefined') {
                this.getColorSchemeInfo(id);
            }
        },
        methods: {
            async saveColorScheme(event) {
                event.preventDefault();
                await axios.post("/user/color-scheme", this.color_scheme)
                    .then((response) => {
                        // Show success
                    });
                    //Show error aswell
            },
            async getColorSchemeInfo(id) {
                await axios.get("/user/color-scheme", { params: { id: id } } )
                    .then((response) => {
                        this.color_scheme = response.data.color_scheme
                    });
                    //Show error aswell
            }
        }
    }

</script>