<template>
    <div class="block-with-sidebar">
        <h1>All templates</h1>
        <ul v-if="templates.length > 0">
            <li v-for="(template, key) in templates" :key="key">
                {{template.title}}
                <router-link :to="{name: 'edit-template', params: { id: template.id } }" class="btn btn-primary">Edit</router-link>
            </li>
        </ul>
        <div v-else>
            <span>No templates</span>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    
    export default {
        data() {
            return {
                templates: []
            }
        },
        created(){
            this.getAllTemplates();
        },
        methods: {
            async getAllTemplates(){
                await axios.get("/user/templates")
                    .then((response) => {
                        this.templates = response.data.templates;
                    });
            }
        }
    }

</script>