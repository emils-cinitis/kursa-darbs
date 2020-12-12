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
                    { name: 'All color schemes', router_name: 'all-color-schemes' },
                    { name: 'Add color scheme', router_name: 'add-color-scheme' }
                ],
            }
        },
        components: { SideNavigation }
    }
</script>