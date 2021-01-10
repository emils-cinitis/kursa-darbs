<template>
    <div class="block-with-sidebar">
        <h1>This is an irreversible action!</h1>
        <p>Deleting the user will also delete all the banners, banner templates and color schemes created.</p>
        <b-button @click="showPopup()" variant="danger">Delete user</b-button>
        <b-modal id="user-delete-modal" hide-footer>
            <h3>Do you really want to delete this user?</h3>

            <b-form-group
                label="Enter your password:"
                label-for="user-password">
                <b-form-input 
                    id="user-password"
                    v-model="password"
                    type="password"
                ></b-form-input>
            </b-form-group>
            <b-row v-if='error'>
                    <b-col cols="12">
                        <p>{{error}}</p>
                    </b-col>
                </b-row>

            <b-button @click="$bvModal.hide('user-delete-modal')">Cancel</b-button>
            <b-button @click="deleteUser" variant="danger">Delete</b-button>
        </b-modal>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        data() {
            return {
                password: '',
                error: ''
            }
        },
        methods: {
            showPopup() {
                this.$bvModal.show('user-delete-modal');
            },
            async deleteUser(){
                await axios.delete("/user/delete", { params: { password: this.password } })
                    .then((response) => {
                        this.$auth.fetch();
                        this.$router.push({name: 'home'})
                    })
                    .catch(error => {
                        this.error = error.response.data.message;
                    });
            }
        }
    }
</script>