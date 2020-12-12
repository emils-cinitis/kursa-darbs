<template>
    <b-row class='pt-2'>
        <b-col cols='5'>
            <div class='p-2 border'>
                <preview :banner="banner" />
            </div>
        </b-col>
        <b-col cols='7'>
            Created By : {{banner.created_by_name}} TODO LINK PROFILE <br>
            Created At : {{banner.created_at}} <br>
            Updated At : {{banner.updated_at}} <br>
            Views : TODO <br>
            Downloads : TODO <br>
            Download: TODO <br>
        </b-col>
    </b-row>
</template>
<script>
    import axios from 'axios';
    import Preview from '../Banners/Preview';
    
    export default {
        data() {
            return {
                banner: { }
            }
        },
        components: { Preview },
        created(){
            if(this.$route.params.uuid) {
                this.banner.uuid = this.$route.params.uuid;
                this.getBanner();
            }
        },
        methods: {
            async getBanner() {
                await axios.get("/banner", {params: { uuid: this.banner.uuid }} )
                    .then((response) => {
                        this.banner = response.data.banner;
                    });
            }
        }
    }

</script>