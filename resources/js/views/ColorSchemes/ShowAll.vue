<template>
    <div class="block-with-sidebar">
        <h1>All color schemes</h1>
        <ul v-if="color_schemes.length > 0">
            <li v-for="(color_scheme, key) in color_schemes" :key="key">
                {{color_scheme.title}}
                <router-link :to="{name: 'edit-color-scheme', params: { id: color_scheme.id } }" class="btn btn-primary">Edit</router-link>
            </li>
        </ul>
        <div v-else>
            <span>No color schemes</span>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    
    export default {
        data() {
            return {
                color_schemes: []
            }
        },
        created(){
            this.getAllColorSchemes();
        },
        methods: {
            async getAllColorSchemes(){
                await axios.get("/user/color-schemes")
                    .then((response) => {
                        this.color_schemes = response.data.color_schemes;
                    });
            }
        }
    }

</script>