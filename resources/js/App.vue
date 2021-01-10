<template>
    <div>
        <navigation :user='user'></navigation>
        <div class='container-fluid'>
            <router-view :user='user'></router-view>
        </div>

        <b-modal id="loginModal" hide-footer title="Login" size="m">
            <login :modal="true" />
        </b-modal>
        <b-modal id="registerModal" hide-footer title="Register" size="m">
            <register :modal="true" />
        </b-modal>
        <b-modal id="resetPasswordModal" hide-footer title="Reset password" size="m">
            <password-reset />
        </b-modal>
    </div>
</template>
<script>    
    import Navigation from './components/Navigation.vue';
    import Login from './views/Login.vue';
    import Register from './views/Register.vue';
    import PasswordReset from './views/ResetPassword.vue';

    export default {
        computed: {
            user() {
                return this.$auth.user() || {};
            }
        },
        components: { Navigation, Login, Register, PasswordReset }
    }
</script>