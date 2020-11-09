<template>
    <div class="block-with-sidebar">
        <b-form @submit="saveBanner">
            <b-form-group
                label="Name:"
                label-for="banner-name"
            >
                <b-form-input 
                    id="banner-name"
                    v-model="banner.name" 
                    placeholder="Enter banner name" 
                    type="text"
                ></b-form-input>
            </b-form-group>
            <b-row v-if='errors.name' class='error-message'>
                <b-col cols="12">
                    <p>{{ errors.name[0] }}</p>
                </b-col>
            </b-row>
            <b-form-group
                label="Banner text:"
                label-for="banner-text"
            >
                <b-form-input 
                    id="banner-text"
                    v-model="banner.main_text"
                    placeholder="Enter banner text" 
                    type="text"
                ></b-form-input>
            </b-form-group>
            <b-row v-if='errors.text' class='error-message'>
                <b-col cols="12">
                    <p>{{ errors.text[0] }}</p>
                </b-col>
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
    export default {
        data() {
            return {
                banner: {
                    name: '',
                    main_text: ''
                },
                errors: {
                    name: '',
                    text: ''
                }
            }
        },
        created(){
            let uuid = this.$route.params.uuid;
            
            if(typeof uuid != 'undefined') {
                this.getBannerInfo(uuid);
            }
        },
        methods: {
            async saveBanner(){
                await axios.post("/user/banners", this.banner)
                    .then((response) => {
                        // Show success
                    })
                    .catch((error) => {
                        this.errors = error.response.data.messages;
                    });
            },
            async getBannerInfo(uuid) {
                await axios.get("/user/banner", {params: { uuid: uuid }} )
                    .then((response) => {
                        this.banner = response.data.banner
                    });
                    //Show error aswell
            }
        }
    }

</script>