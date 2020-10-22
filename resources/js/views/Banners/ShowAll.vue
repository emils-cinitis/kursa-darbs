<template>
    <div class="block-with-sidebar">
        <h1>All banners</h1>
        <ul>
            <li v-for="(banner, key) in banners" :key="key">
                {{banner.name}}
                <b-button @click="$router.push({name: 'edit-banner', params: { uuid: banner.uuid } })">Edit</b-button>
            </li>
        </ul>
    </div>
</template>
<script>
    import axios from 'axios';
    
    export default {
        data() {
            return {
                banners: []
            }
        },
        created(){
            this.getAllBanners();
        },
        methods: {
            async getAllBanners(){
                await axios.get("/user/banners")
                    .then((response) => {
                        this.banners = response.data.banners;
                        console.log(this.banners);
                    });
            }
        }
    }

</script>