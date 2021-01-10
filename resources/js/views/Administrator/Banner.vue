<template>
    <div class="block-with-sidebar">
        <!-- Main content -->
        <b-row v-if="exception == ''">
            <h1 class="w-100">User information</h1>
            <div v-if="!loading">
                <b-row>
                    <b-col cols="6">
                        <b-row>
                            <!-- INFO -->
                            <b-col cols='12'>
                                Created By : {{ banner.created_by_name }}
                            </b-col>
                            <b-col cols='12'>
                                Created At : {{ new Date(banner.created_at).toLocaleDateString("en-GB") }}
                            </b-col>
                            <b-col cols='12'>
                                Updated At : {{ new Date(banner.updated_at).toLocaleDateString("en-GB") }}
                            </b-col>
                            <b-col cols="6">
                                <b-button
                                    @click="$bvModal.show('deleteModal')"
                                    variant="danger"
                                    class="w-100"
                                >Delete</b-button>
                            </b-col>
                            <b-col cols="6">
                                <!-- ToDo: fix this shit -->
                                <router-link 
                                    :to="{ name: 'admin-show-user', params: { uuid: banner.created_by_name } }"
                                    class="w-100 btn btn-theme-blue-light text-white my-auto"
                                >Show user</router-link>
                            </b-col>
                        </b-row>
                    </b-col>
                    <b-col cols="6">
                        <preview 
                            v-if="typeof banner.banner_types == 'object'"
                            :banner="banner" 
                        />
                        <div v-else>
                            <span class="error-text-single">{{ preview_exception }}</span>
                        </div>
                    </b-col>
                </b-row>
            </div>
            <div v-else class="d-block">
                <span>Loading...</span>
            </div>
            <!-- Delete popup -->
            <delete-popup 
                :deletable_item="'Banner'" 
                @delete_item="deleteBanner"
            />
        </b-row>
        <!-- Exception error -->
        <div v-else>
            <span class="error-text-single">{{ exception }}</span>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import Preview from '../Banners/Preview.vue';
    import DeletePopup from '../../components/DeletePopup.vue';
    import { Cropper } from 'vue-advanced-cropper';
    import 'vue-advanced-cropper/dist/style.css';

    export default {
        data() {
            return {
                banner: {},
                loading: true,
                exception: '',
                preview_exception: ''
            }
        },
        components: { Preview, DeletePopup },
        created(){
            let uuid = this.$route.params.uuid;
            this.getBannerInfo(uuid);
        },
        methods: {

            //Get existing banner info
            async getBannerInfo(uuid) {
                await axios.get("/banner", { params: { uuid: uuid } } )
                    .then((response) => {
                        this.banner = response.data.banner;
                        this.loading = false;
                    })
                    .catch((error) => {
                        // Exception
                        this.exception = error.response.data.message;
                        this.loading = false;
                    });
            },
            deleteBanner() {
                return false;
            }
        }
    }

</script>