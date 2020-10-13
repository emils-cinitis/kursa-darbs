<template>
    <b-row>
        <h1>Login</h1>
        <b-col cols="12">
            <form action=""></form>
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
                    <b-button @click="login" variant="success">Login</b-button>
                </b-col>
            </b-row>
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
                remember_me: false
            }
        },
        methods: {
            login(){
                var app = this;
                this.$auth.login({
                    data: app.user,
                    success: function() { },
                    error: function() { },

                    rememberMe: app.remember_me
                });
            },
        }
    }
</script>