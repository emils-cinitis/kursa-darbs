<template>
    <div>
        <h1>Show all banners</h1>
        <ul>
            <li v-for="(banner, key) in banners" :key="key">
                {{banner.name}}
            </li>
        </ul>
    </div>
</template>
<script>
    import axios from 'axios'
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
                    });
            }
        }
    }

</script>