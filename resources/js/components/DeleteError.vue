<template>
    <b-modal id="deleteError" hide-footer :title="deletable_item + ' in use'" @hide="closePopup">
        <h3>This {{ deletable_item }} is already in use!</h3>
        <p>Cannot delete {{ deletable_item }} whilst it's in use by any banner</p>
        <p>Used in these banners: </p>
        <ul>
            <li v-for='(banner, key) in banners' :key='key'>
                <router-link :to="{name: 'edit-banner', params: { uuid: banner.uuid } }">{{ banner.name }}</router-link>
            </li>
        </ul>
        <b-button 
            variant="theme-blue-dark" 
            class="text-white w-100" 
            @click="closePopup"
        >Close</b-button>
    </b-modal>
</template>

<script>
    export default {
        props: ['deletable_item', 'banners'],
        methods: {
            closePopup() {
                this.$bvModal.hide('deleteError');
                this.$emit('close_popup');
            },
        }
    }
</script>