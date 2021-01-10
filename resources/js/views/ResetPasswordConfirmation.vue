<template>
    <b-row>
        <b-col cols="12">
            <b-form id="reset-form" @submit="resetPassword" class="form-small mx-auto p-3">
                <h1>Reset password</h1>
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

                <b-row v-if='error'>
                    <b-col cols="12">
                        <p>{{error}}</p>
                    </b-col>
                </b-row>

                <b-row>
                    <b-col cols="12">
                        <b-button type="submit" variant="success">Reset password</b-button>
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
                    uuid: '',
                    password: '',
                    password_confirmation: ''
                },
                error: '',
                errors: {
                    password: ''
                }
            }
        },
        params: ['uuid'],
        created() {
            this.user.uuid = this.$route.params.uuid;
        },
        methods: {
            resetPassword(event){
                event.preventDefault();
                this.errors = {};
                this.error = '';

                axios.post("/user/reset", this.user)
                    .then((response) => {
                        this.$bvModal.show('loginModal');
                    })
                    .catch(error => {
                        if(error.response.data.message) {
                            this.error = error.response.data.message;
                        } 
                        if(error.response.data.messages) {
                            this.errors = error.response.data.messages;
                        }
                    });
            },
        }
    }
</script>