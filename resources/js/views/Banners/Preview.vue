<template>
    <div ref="preview-container">
        <div 
            v-for="(banner_type, key) in banner.banner_types" 
            :key='key' 
            :ref='key'
            :class="'pb-2 preview-block-container template-block-' + key"
        >
            <div
            :class="'position-relative preview-' + key"
            :style="'width:' + (banner_type.sizes.width * scale) + 'px;' +
                    'height:' + (banner_type.sizes.height * scale) + 'px;' +
                    'background-color:' + banner.color_scheme.background_color
            ">
                <div 
                    v-for="(block, key1) in banner_type.blocks" 
                    :key='key1'
                    :class="
                        'position-absolute overflow-hidden' +
                        ((block.block_type.id == 1 || block.block_type.id == 2) ? ' default-text' : '') +
                        ((block.block_type.id == 4) ? ' cta-button text-center' : '')"
                    :style="
                        'top:' + (block.top_offset * scale) + 'px;' +
                        'left:' + (block.left_offset * scale) + 'px;' +
                        'width:' + (block.width * scale) + 'px;' +
                        'height:' + (block.height * scale) + 'px;' +
                        'z-index: ' + block.z_index + ';' +
                        'font-size:' + 
                            ((block.block_type.id == 1) ? (32 * scale) : '') +
                            ((block.block_type.id == 2) ? (28 * scale) : '') +
                            ((block.block_type.id == 4) ? (16 * scale) : '') +
                            'px;' +
                        'color: ' + banner.color_scheme.text_color + ';' +
                        
                        ((block.block_type.id == 4) 
                            ? 'background-color:' + banner.color_scheme.cta_color
                            : '')
                    ">
                    <span 
                        v-if="block.block_type.id !== 3"
                        class="d-block w-100"
                    >
                        {{ banner[block.block_type.title] }}
                    </span>
                    <!-- Todo TEST -->
                    <img v-else :src="banner.image" alt="Image">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                scale: 1,
            }
        },
        props: {
            banner: Object
        },
        mounted() {
            window.addEventListener("resize", this.scaleElements);
        },
        updated() {
            this.scaleElements();
        },
        methods: {
            scaleElements() {
                let container = this.$refs["preview-container"],
                    max_banner_width = 0;

                //Find biggest banner width
                for( const [key, banner_type]  of Object.entries(this.banner.banner_types)) {
                    max_banner_width = (banner_type.sizes.width > max_banner_width) ? banner_type.sizes.width : max_banner_width;
                }

                //Scale preview
                this.scale = container.clientWidth / max_banner_width;
                if(this.scale > 1) {
                    this.scale = 1;
                }
            }
        }
    }
</script>