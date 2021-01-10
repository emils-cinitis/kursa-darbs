°<template>
    <b-modal id="warning" hide-footer :title="model + ' in use'" no-close-on-backdrop>
        <h3>This {{model}} is already in use!</h3>
        <p>By modifying this {{ model }}, all existing banners will change to the updated {{model}}</p>
        <p>Used in these banners: </p>
        <ul>
            <li v-for='(banner, key) in banners' :key='key'>
                <router-link :to="{name: 'edit-banner', params: { uuid: banner.uuid } }">{{ banner.name }}</router-link>
            </li>
        </ul>
        <b-button 
            class="mt-3 text-white" 
            block 
            variant="theme-blue-dark" 
            @click="$bvModal.hide('warning');"
        >Cancel</b-button>

        <b-button 
            class="mt-3" 
            variant="danger" 
            block 
            @click="editItem"
        >Save</b-button>
    </b-modal>
</template>

<script>
    export default {
        props: ['model', 'banners'],
        methods: {
            editItem() {
                this.$emit('edit_item');
                this.$bvModal.hide('warning');
            }
        }
    }
</script>