<template>
    <div>
        <b-row style="height: calc(100vh - 56px);">
            <b-col cols="6" class="px-0">
                <a @click="redirectClickOnAddBanner" href="#">
                    <div class="w-100 h-100 background-theme-blue-default text-white text-center d-flex fp-block-left">
                        <span class="w-100 align-self-center">
                            Create banner
                        </span>
                    </div>
                </a>
            </b-col>
            <b-col cols="6" class="px-0">
                <router-link :to="{name: 'public-banners'}">
                    <div class="w-100 h-100 background-theme-blue-light text-white text-center d-flex fp-block-right">
                        <span class="w-100 align-self-center">
                            See all created banners
                        </span>
                    </div>
                </router-link>
            </b-col>
        </b-row>
    </div>
</template>

<script>
export default {
    props: ['user'],
    methods: {
        redirectClickOnAddBanner() {
            if(typeof this.user.email == 'undefined') {
                this.$bvModal.show('loginModal');
            } else {
                this.$router.push({name: "add-banner"});
            }
        }
    }
}
</script>