<template>
    <b-row>
        <b-col cols="12">
            <b-form id="login-form" @submit="login" :class="'form-small mx-auto ' + ((modal) ? 'no-border' : 'p-3')">
                <h1 v-if="!modal">Login</h1>
                <b-form-group>
                    <b-form-input 
                        v-model="user.email" 
                        placeholder="Enter your email" 
                        type="email"
                    ></b-form-input>
                </b-form-group>

                <b-form-group>
                    <b-form-input 
                        v-model="user.password"
                        type="password"
                    ></b-form-input>
                </b-form-group>
                <b-row v-if='error'>
                    <b-col cols="12">
                        <p>{{error}}</p>
                    </b-col>
                </b-row>

                <b-form-group>
                    <b-form-checkbox
                        id="remember_me"
                        v-model="remember_me"
                    >
                    Remember me
                    </b-form-checkbox>
                </b-form-group>
                <b-row>
                    <b-col cols="12">
                        <b-button type="submit" variant="theme-blue-dark" class="text-white">Login</b-button>
                    </b-col>
                </b-row>

                <b-row>
                    <b-col cols="12">
                        <span>Don't have an account? Create one <a @click="showRegisterModal" href="#">here</a>!</span>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="12">
                        <span>Forgot password? Reset it <a @click="showPasswordReset" href="#">here</a>!</span>
                    </b-col>
                </b-row>
            </b-form>
        </b-col>
    </b-row>
</template>

<script>
    export default {
        data() {
            return {
                user: {
                    email: '',
                    password: '',
                },
                remember_me: false,
                error: ''
            }
        },
        props: {
            modal: Boolean
        },
        methods: {
            login(e) {
                e.preventDefault();

                var app = this;
                var redirectObject = this.$auth.redirect();

                this.$auth.login({
                    data: app.user,
                    success: function() {
                        if(app.modal) {
                            app.$bvModal.hide('loginModal');
                        }
                    },
                    error: function(error) {
                        this.error = error.response.data.message;
                    },
                    redirect: { name: redirectObject ? redirectObject.from.name : 'home' },
                    rememberMe: app.remember_me
                });
            },
            showRegisterModal() {
                if(this.modal) {
                    this.$bvModal.hide('loginModal');
                    this.$bvModal.show('registerModal');
                } else {
                    this.$router.push({name: 'register'});
                }
            },
            showPasswordReset() { 
                this.$bvModal.hide('loginModal');
                this.$bvModal.show('resetPasswordModal');
            }
        }
    }
</script>