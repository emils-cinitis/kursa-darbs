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
                    { name: 'Edit user information', router_name: 'edit-user' },
                    { name: 'Delete user', router_name: 'delete-user' }
                ],
            }
        },
        components: { SideNavigation }
    }
</script>