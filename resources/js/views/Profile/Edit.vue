<template>
    <div class="block-with-sidebar">
        <b-row v-if="exception == ''">
            <b-col cols="12">
                <b-form id="edit-user-form" @submit="edit" class="form-small mx-auto">
                    <h1 class="w-100 mx-auto">Edit information</h1>
                    <b-form-group
                        label="Name:"
                        label-for="user-name"
                    >
                        <b-form-input 
                            id="user-name"
                            v-model="user.username" 
                            placeholder="Enter your username" 
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
                        <b-col cols="12" class="text-center">
                            <b-button type="submit" class="btn-form" variant="success">Edit</b-button>
                        </b-col>
                    </b-row>
                </b-form>
            </b-col>
        </b-row>
        <div v-else>
            <span class="error-text-single">{{ exception }}</span>
        </div>
    </div>
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
                },
                exception: ''
            }
        },
        created(){
            this.loadUserInformation();
        },
        methods: {
            async loadUserInformation(){
                await axios.get("/user/all")
                    .then((response) => {
                        this.user = response.data.data;
                    })
                    .catch((error) => {
                        this.exception = error.response.data.message
                    });
            },
            async edit(event){
                event.preventDefault();
                if(this.validateInputs()) {
                    await axios.post("/user/store", this.user)
                        .then((response) => {
                            this.errors = {};
                            
                            this.$bvToast.toast(response.data.message, {
                                title: 'Success',
                                variant: 'theme-blue-default',
                                solid: true,
                                appendToast: true,
                                autoHideDelay: 10000
                            });
                        })
                        .catch(res => {
                            this.errors = res.response.data.messages;
                        });
                }
            },
            validateInputs(){
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