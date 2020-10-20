<template>
    <div>
        <navigation :user='user'></navigation>
        <div class='container-fluid'>
            <router-view :user='user'></router-view>
        </div>
    </div>
</template>
<script>    
    import Navigation from './components/Navigation.vue';
    export default {
        computed: {
            user() {
                return this.$auth.user() || {};
            }
        },
        components: { Navigation }
    }
</script>