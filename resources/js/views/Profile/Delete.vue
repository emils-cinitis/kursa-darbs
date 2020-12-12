<template>
    <div class="block-with-sidebar">
        <b-button @click="showPopup()" variant="danger">Delete user</b-button>
        <b-modal id="user-delete-modal" hide-footer>
            <h3>Do you really want to delete this user?</h3>
            <b-button @click="$bvModal.hide('user-delete-modal')">Cancel</b-button>
            <b-button @click="deleteUser" variant="danger">Delete</b-button>
        </b-modal>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        methods: {
            showPopup() {
                this.$bvModal.show('user-delete-modal');
            },
            async deleteUser(){
                await axios.delete("/user/delete")
                    .then((response) => {})
                    .catch(error => {
                        console.log(error)
                    });
            }
        }
    }
</script>