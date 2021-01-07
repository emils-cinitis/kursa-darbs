<template>
    <div class='pt-2'>
        <b-row v-if="exception == ''">
            <b-col cols='5'>
                <div class='p-2 border'>
                    <preview 
                        v-if="typeof banner.banner_types == 'object'"
                        :banner="banner" 
                    />
                </div>
            </b-col>
            <b-col cols='7'>
                <b-row>
                    <b-col cols='12'>
                        Created By : {{ banner.created_by_name }}
                    </b-col>
                    <b-col cols='12'>
                        Created At : {{ new Date(banner.created_at).toLocaleDateString("en-GB") }}
                    </b-col>
                    <b-col cols='12'>
                        Updated At : {{ new Date(banner.updated_at).toLocaleDateString("en-GB") }}
                    </b-col>
                    <b-col cols='12'>
                        <b-button
                            @click="exportBanner"
                        >
                            Download
                        </b-button>
                    </b-col>
                </b-row>
            </b-col>
        </b-row>
        <div v-else>
            <span class="error-text-single">{{ exception }}</span>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import Preview from '../Banners/Preview';
    
    export default {
        data() {
            return {
                banner: {},
                exception: ''
            }
        },
        components: { Preview },
        created(){
            if(this.$route.params.uuid) {
                this.getBanner(this.$route.params.uuid);
            }
        },
        methods: {
            getBanner(uuid) {
                axios.get("/banner", { params: { uuid: uuid } } )
                    .then((response) => {
                        this.banner = response.data.banner;
                    })
                    .catch((error) => {
                        this.exception = error.response.data.message;
                    });
            },

            exportBanner() {
                axios.get("/banner/export", { params: { uuid: this.banner.uuid }, responseType: 'arraybuffer' } )
                    .then((response) => {
                        let blob = new Blob( [response.data] , { type:'application/*' });
                        let link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = this.banner.name + '.zip';
                        link.click();
                    })
                    .catch((error) => {
                        let message = (error.response.data.message) ? error.response.data.message : "Fatal error";

                        this.$bvToast.toast(message, {
                            title: 'Error',
                            variant: 'danger',
                            solid: true,
                            appendToast: true,
                            autoHideDelay: 10000
                        });
                    });
            }
        }
    }

</script>