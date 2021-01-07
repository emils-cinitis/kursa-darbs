<template>
    <b-modal id="deleteModal" hide-footer :title="'Delete ' + deletable_item" @hide="closePopup" size="lg">
        <h3>Are you sure you want to delete this {{ deletable_item }}?</h3>
        <p>This cannot be undone!</p>
        <b-button 
            variant="theme-blue-dark"
            block
            class="text-white col" 
            @click="closePopup"
        >Close</b-button>
        <b-button 
            variant="danger"
            block
            class="text-white col" 
            @click="deleteItem"
        >Delete</b-button>
    </b-modal>
</template>

<script>
    export default {
        props: ['deletable_item'],
        methods: {
            closePopup() {
                this.$bvModal.hide('deleteModal');
                this.$emit('close_delete');
            },
            deleteItem() {
                this.$emit('delete_item');
                this.$bvModal.hide('deleteModal');
            }
        }
    }
</script>