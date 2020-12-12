<template>
    <div class="side-navigation">
        <ul>
            <li v-for="(element, key) in elements" :key='key'>
                <router-link :to="{ name: element.router_name }">{{ element.name }}</router-link>
            </li>
        </ul>
    </div>
</template>
<script>
    export default {
        props: ['elements']
    }
</script>