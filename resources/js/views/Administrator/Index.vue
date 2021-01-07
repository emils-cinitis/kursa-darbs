<template>
    <b-row>
        <side-navigation class="col-2" :elements='side_navigation_elements' />
        <router-view class="col-10" />
    </b-row>
</template>

<script>
    import SideNavigation from '../../components/SideNavigation';

    export default {
        data() {
            return {
                side_navigation_elements: [
                    { name: 'All users', router_name: 'admin-all-users' },
                    { name: 'All banners', router_name: 'admin-all-banners' }
                ],
            }
        },
        components: { SideNavigation }
    }
</script>