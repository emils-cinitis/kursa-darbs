<template>
    <div>
        <ul>
            <li v-for="(element, key) in elements" :key='key'>
                <a @click="$router.push({name: element.router_name})">{{ element.name }}</a>
            </li>
        </ul>
    </div>
</template>
<script>
    export default {
        props: ['elements']
    }
</script>