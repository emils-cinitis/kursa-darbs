<template>
    <b-row>
        <b-col cols="12">
            <b-form id="register-form" @submit="register" :class="'form-small mx-auto ' + ((modal) ? 'no-border' : 'p-3')">
                <h1 v-if="!modal">Register</h1>
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
                <b-row v-if='errors.username' class='error-message'>
                    <b-col cols="12">
                        <p>{{ errors.username[0] }}</p>
                    </b-col>
                </b-row>

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
                <b-row v-if='errors.email' class='error-message'>
                    <b-col cols="12">
                        <p>{{ errors.email[0] }}</p>
                    </b-col>
                </b-row>

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
                <b-row v-if='errors.password' class='error-message'>
                    <b-col cols="12">
                        <p>{{ errors.password[0] }}</p>
                    </b-col>
                </b-row>

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

                <b-row>
                    <b-col cols="12">
                        <span>Already have an account? Click <a @click="showLoginModal" href="#">here</a> to login!</span>
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
                },
                errors: {
                    username: '',
                    email: '',
                    password: ''
                }
            }
        },
        props: {
            modal: Boolean
        },
        methods: {
            register(event){
                event.preventDefault();
                var app = this;
                if(this.validateInputs()) {
                    this.$auth.register({
                        data: app.user,
                        success: function () {
                            this.$bvModal.hide('registerModal');
                            this.$bvModal.show('loginModal');
                        },
                        error: function (res) {
                            app.errors = res.response.data.messages;
                        }
                    });
                }
            },
            showLoginModal() {
                if(this.modal) {
                    this.$bvModal.hide('registerModal');
                    this.$bvModal.show('loginModal');
                } else {
                    this.$router.push({name: 'login'});
                }
            },
            validateInputs() {
                let inputs = document.querySelectorAll("input");
                var correct = true;
                inputs.forEach((input) => {
                    if(!input.checkValidity()) {
                        input.reportValidity();
                        correct = false;
                    }
                });
                return correct;
            }
        }
    }
</script>