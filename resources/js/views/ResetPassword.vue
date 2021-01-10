<template>
    <b-row>
        <b-col cols="12">
            <b-form id="login-form" @submit="resetPassword" class="form-small mx-auto no-border">
                <h1>Reset password</h1>
                <b-form-group>
                    <b-form-input 
                        v-model="email" 
                        placeholder="Enter your email" 
                        type="email"
                    ></b-form-input>
                </b-form-group>

                <b-row v-if='error'>
                    <b-col cols="12">
                        <p>{{error}}</p>
                    </b-col>
                </b-row>

                <b-row>
                    <b-col cols="12">
                        <b-button type="submit" variant="theme-blue-dark" class="text-white">Reset password</b-button>
                    </b-col>
                </b-row>

                <b-row>
                    <b-col cols="12">
                        <span>Don't have an account? Create one <a @click="showRegisterModal" href="#">here</a>!</span>
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
                email: '',
                error: ''
            }
        },
        methods: {
            resetPassword(event) {
                event.preventDefault();

                axios.get("/user/reset", { params: { email: this.email } })
                    .then((response) => {
                        this.error = '';

                        this.$bvToast.toast(response.data.message, {
                            title: 'Success',
                            variant: 'theme-blue-default',
                            solid: true,
                            appendToast: true,
                            autoHideDelay: 10000
                        });
                    })
                    .catch(error => {
                        this.error = error.response.data.message;
                    });
            },
            showRegisterModal() {
                this.$bvModal.hide('resetPasswordModal');
                this.$bvModal.show('registerModal');
            }
        }
    }
</script>