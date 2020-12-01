<template>
    <div class="block-with-sidebar">
        <h1>All color schemes</h1>
        <b-row v-if="color_schemes.length > 0">
            <b-col class="col-12 col-md-4" v-for="(color_scheme, key) in color_schemes" :key="key">
                <div class="color-scheme-container overflow-hidden text-center">
                    <div class="color-scheme-title">
                        {{ color_scheme.title }}
                    </div>
                    <router-link :to="{name: 'edit-color-scheme', params: { id: color_scheme.id } }" class="btn btn-primary my-2">Edit</router-link>
                    <button 
                        v-if="typeof color_scheme.preview == 'undefined' || color_scheme.preview == false"
                        @click="setPreview(key, color_scheme)" 
                        class="btn btn-primary my-2"
                    >Preview</button>
                    <preview 
                        v-else
                        :background_color="color_scheme.background_color"
                        :text_color="color_scheme.text_color"
                        :cta_color="color_scheme.cta_color"
                    />
                </div>
            </b-col>
        </b-row>
        <div v-else>
            <span>No color schemes</span>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import Preview from './Preview';
    
    export default {
        data() {
            return {
                color_schemes: []
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
                    });
            },
            setPreview(key, color_scheme) {
                color_scheme.preview = true;
                this.$set(this.color_schemes, key, color_scheme);
            }
        }
    }

</script>