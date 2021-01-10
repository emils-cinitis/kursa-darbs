<template>
    <div class="block-with-sidebar">
        <div v-if="exception == ''">
            <h1>User information</h1>
            <div v-if="!loading">
                <b-row>
                    <b-col cols="12">
                        <b-row>
                            <!-- User info -->
                            <b-col cols="12">
                                <span>Username: <b>{{ user.username }}</b></span>
                            </b-col>
                            <b-col cols="12">
                                <span>Email: <b>{{ user.email }}</b></span>
                            </b-col>
                            <b-col cols="12">
                                <span>Role: <b>{{ user.role.role }}</b> </span>
                            </b-col>
                            <b-col cols="12">
                                <span>Color scheme count: <b>{{ user.color_scheme_count }}</b> </span>
                            </b-col>
                            <b-col cols="12">
                                <span>Template count: <b>{{ user.template_count }}</b> </span>
                            </b-col>
                            <b-col cols="12">
                                <span>Banner count: <b>{{ user.banner_count }}</b> </span>
                            </b-col>

                            <!-- Buttons -->
                            <b-col cols="3">
                                <router-link 
                                    :to="{name: 'admin-all-banners', params: { user_uuid: user.uuid } }"
                                    class="w-100 mb-1 btn btn-theme-blue-light text-white"
                                >Show user banners</router-link>
                            </b-col>
                            <b-col cols="3" v-if="user.user_role !== 1">
                                <b-button
                                    @click="showBlockModal"
                                    variant="danger"
                                    class="w-100"
                                >Block</b-button>
                            </b-col>
                            <b-col cols="3" v-if="user.user_role !== 2">
                                <b-button
                                    @click="showStandardModal"
                                    variant="danger"
                                    class="w-100"
                                >Make standard user</b-button>
                            </b-col>
                            <b-col cols="3" v-if="user.user_role !== 3">
                                <b-button
                                    @click="showAdminModal"
                                    variant="danger"
                                    class="w-100"
                                >Make admin</b-button>
                            </b-col>
                            <b-col cols="3">
                                <b-button
                                    @click="$bvModal.show('deleteModal')"
                                    variant="danger"
                                    class="w-100"
                                > Delete</b-button>
                            </b-col>
                        </b-row>
                    </b-col>
                </b-row>
            </div>
            <div v-else>
                <span>Loading...</span>
            </div>
            <!-- Delete popup -->
            <delete-popup 
                :deletable_item="'User'" 
                @delete_item="deleteUser"
            />

            <!-- Block popup -->
            <b-modal id="specialModal" hide-footer :title="special_modal_title" size="lg">
                <h3>{{special_modal_text}}</h3>
                <b-button 
                    variant="theme-blue-dark"
                    block
                    class="text-white col" 
                    @click="$bvModal.hide('specialModal')"
                >Close</b-button>
                <b-button 
                    variant="danger"
                    block
                    class="text-white col" 
                    @click="changeRoleForUser"
                >{{special_modal_title}}</b-button>
            </b-modal>
        </div>
        <div v-else>
            <span class="error-text-single">{{ exception }}</span>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import DeletePopup from '../../components/DeletePopup.vue';
    
    export default {
        data() {
            return {
                user: {},
                exception: '',
                special_modal_text: '',
                special_modal_title: '',
                new_role: 0,
                loading: true,
            }
        },
        components: { DeletePopup },
        created() {
            if(this.$route.params.uuid) {
                this.getUser(this.$route.params.uuid);
            } else {
                this.exception = 'No user UUID specified';
            }
        },
        methods: {
            //Get all user users from database
            async getUser(uuid){
                await axios.get("/admin/user", { params: { uuid: uuid } } )
                    .then((response) => {
                        this.user = response.data.user;
                        this.loading = false;
                    })
                    .catch((error) => {
                        this.exception = error.response.data.message;
                        this.loading = false;
                    });
            },

            //Change user role
            changeRoleForUser() {
                axios.post("/admin/user", { uuid: this.user.uuid, role: this.new_role } )
                    .then((response) => {
                        this.getUser(this.user.uuid);

                        this.$bvModal.hide('specialModal');
                        this.$bvToast.toast(response.data.message, {
                            title: 'Success',
                            variant: 'theme-blue-default',
                            solid: true,
                            appendToast: true,
                            autoHideDelay: 10000
                        });
                    })
                    .catch((error) => {
                        this.$bvModal.hide('specialModal');
                        this.$bvToast.toast(error.response.data.message, {
                            title: 'Error',
                            variant: 'danger',
                            solid: true,
                            appendToast: true,
                            autoHideDelay: 10000
                        });
                    });
            },

            //Delete user from database along with all of their banners
            deleteUser() {
                axios.delete("/admin/user", { params: { uuid: this.user.uuid } } )
                    .then((response) => {
                        this.$router.go(-1);
                    })
                    .catch((error) => {
                        this.$bvToast.toast(error.response.data.message, {
                            title: 'Error',
                            variant: 'danger',
                            solid: true,
                            appendToast: true,
                            autoHideDelay: 10000
                        });
                    });
            },

            //Show modal depending on new role
            showBlockModal() {
                this.special_modal_title = 'Block user';
                this.special_modal_text = 'Are you sure you want to block this user?';
                this.new_role = 1;

                this.$bvModal.show('specialModal');
            },
            showStandardModal() {
                this.special_modal_title = 'Make user standard';
                this.special_modal_text = 'Are you sure you want to make this user as a standard user?';
                this.new_role = 2;

                this.$bvModal.show('specialModal');
            },
            showAdminModal() {
                this.special_modal_title = 'Make user admin';
                this.special_modal_text = 'Are you sure you want to make this user an administrator?';
                this.new_role = 3;

                this.$bvModal.show('specialModal');
            },
        }
    }

</script>