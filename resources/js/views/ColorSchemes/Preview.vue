<template>
    <div 
        class="color-scheme-preview-container text-center"
        :style="'background-color: ' + background_color + '; color:' + text_color + ';'"
    >
        <span class="text-preview">Text color</span>
        <span 
            class="cta-preview"
            :style="'background-color:' + cta_color"
        >CTA color</span>
    </div>
</template>
<script>
    export default {
        props: ['background_color', 'text_color', 'cta_color']
    }
</script>