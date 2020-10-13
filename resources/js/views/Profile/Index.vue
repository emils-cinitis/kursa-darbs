<template>
    <div>
        <side-navigation :elements='side_navigation_elements' />
        <router-view></router-view>
    </div>
</template>

<script>
    import SideNavigation from '../../components/SideNavigation';

    export default {
        data(){
            return {
                side_navigation_elements: [
                    { name: 'Edit user information', router_name: 'edit-user' }
                ],
            }
        },
        props: ['user'],
        components: { SideNavigation }
    }
</script>