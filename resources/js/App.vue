<template>
    <div>
        <navigation :user='user'></navigation>
        <div class="py-5">
            <div class='container'>
                <router-view :user='user'></router-view>
            </div>
        </div>
    </div>
</template>
<script>    
    import Navigation from './components/Navigation.vue'
    export default {
        computed: {
            user() {
                return this.$auth.user() || {};
            }
        },
        components: { Navigation }
    }
</script>