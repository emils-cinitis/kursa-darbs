<template>
    <b-row>
        <h1>Register</h1>
        <b-col cols="12">
            <b-form @submit="register">
                <b-form-group
                    label="Username:"
                    label-for="user-name"
                >
                    <b-form-input 
                        id="user-name"
                        v-model="user.username" 
                        placeholder="Enter your name" 
                        type="text"
                        required
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    label="Email:"
                    label-for="user-email"
                >
                    <b-form-input 
                        id="user-email"
                        v-model="user.email" 
                        placeholder="Enter your email" 
                        type="email"
                        required
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    label="Password:"
                    label-for="user-password"
                >
                    <b-form-input 
                        id="user-password"
                        v-model="user.password"
                        type="password"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    label="Password confirmation:"
                    label-for="user-password-confirmation"
                >
                    <b-form-input 
                        id="user-password-confirmation"
                        v-model="user.password_confirmation"
                        type="password"
                    ></b-form-input>
                </b-form-group>
                <b-row>
                    <b-col cols="12">
                        <b-button type="submit" variant="success">Register</b-button>
                    </b-col>
                </b-row>
            </b-form>
        </b-col>
    </b-row>
</template>

<script>
    import axios from 'axios';

    export default {
        data() {
            return {
                user: {
                    username: '',
                    email: '',
                    password: '',
                    password_confirmation: ''
                }
            }
        },
        methods: {
            register(event){
                event.preventDefault();
                if(this.validateInputs()) {
                    var app = this;
                    this.$auth.register({
                        data: app.user,
                        success: function () {
                            app.$router.push({name: 'login', params: {successRegistrationRedirect: true}})
                        },
                        error: function (res) {
                            console.log(res.response.data.errors)
                        }
                    });
                }
            },
            validateInputs(){
                //Validate inputs bootstrap
                return true;
            }
        }
    }
</script>