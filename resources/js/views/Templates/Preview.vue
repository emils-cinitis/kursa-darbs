<template>
    <div ref="preview-container">
        <div v-for="(banner_type, key) in template" :key="key" class="pb-3">
            <div class="d-flex">
                <div :ref="key" :class="'template-block template-block-' + key">
                    <div :class="'position-relative mx-auto preview-' + key"
                        :style="
                            'width: ' + (banner_type.sizes.width * scale) + 'px;' +
                            'height: ' + (banner_type.sizes.height * scale) + 'px;'
                        "
                    >

                        <div v-for="(block, key1) in banner_type.blocks" :key="key1">
                            <div class="position-absolute text-center preview-block"

                                :style="
                                    'top:' + (block.top_offset * scale) + 'px;' +
                                    'left:' + (block.left_offset * scale) + 'px;' +
                                    'width:' + (block.width * scale) + 'px;' +
                                    'height:' + (block.height * scale) + 'px;' +
                                    'z-index: ' + block.z_index + ';'
                                "
                            >
                                <span>{{block.block_type.title}}</span>
                            </div>
                        </div>

                    </div>
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
            template: Object
        },
        mounted() {
            window.addEventListener("resize", this.scaleElements);
            this.runFirstScale(0);
        },
        methods: {
            //Scale on first load
            runFirstScale(iteration) {
                let container = this.$refs["preview-container"];
                //Make sure container has size, or try next tick
                if(typeof container !== "undefined" && container.clientWidth > 0) {
                    this.scaleElements();
                } else if(iteration < 100) {
                    this.$nextTick(() => {
                        this.runFirstScale(iteration+1)
                    });
                }
            },
            //Scale preview from largest banner type
            scaleElements() {
                let container = this.$refs["preview-container"],
                    max_template_width = 0;

                //Find biggest banner width
                for( const [key, banner_type]  of Object.entries(this.template)) {
                    max_template_width = (banner_type.sizes.width > max_template_width) ? banner_type.sizes.width : max_template_width;
                }

                //Scale preview
                if(typeof container !== "undefined") {
                    this.scale = container.clientWidth / max_template_width;
                }
                if(this.scale > 1) {
                    this.scale = 1;
                }
            }
        }
    }
</script>