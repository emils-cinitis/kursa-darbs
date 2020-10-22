<template>
    <div class="block-with-sidebar">
        <h1>Edit information</h1>
        <b-col cols="12">
            <b-form @submit="edit">
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
                    <b-col cols="12" class="btn-container">
                        <b-button type="submit" class="btn-form" variant="success">Edit</b-button>
                    </b-col>
                </b-row>
            </b-form>
        </b-col>
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
                }
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
                    .catch(error => {
                        console.log(error)
                    });
            },
            async edit(event){
                event.preventDefault();
                if(this.validateInputs()) {
                    await axios.post("/user/store", this.user)
                        .then((response) => {
                            console.log(response);
                        })
                        .catch(error => {
                            console.log(error)
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