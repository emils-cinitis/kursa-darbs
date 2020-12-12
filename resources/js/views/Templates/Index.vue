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
                    { name: 'All templates', router_name: 'all-templates' },
                    { name: 'Add tempalte', router_name: 'add-template' }
                ],
            }
        },
        components: { SideNavigation }
    }
</script>