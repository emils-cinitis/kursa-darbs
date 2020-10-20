<template>
    <b-row>
        <side-navigation class="col-2" :elements='side_navigation_elements' />
        <router-view class="col-10"></router-view>
    </b-row>
</template>

<script>
    import SideNavigation from '../../components/SideNavigation';

    export default {
        data() {
            return {
                side_navigation_elements: [
                    { name: 'All banners', router_name: 'all-banenrs' },
                    { name: 'Add banner', router_name: 'add-banner' }
                ],
            }
        },
        components: { SideNavigation }
    }
</script>