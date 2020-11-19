<template>
    <div class="block-with-sidebar">
        <b-form @submit="saveTemplate">
            <!-- Title -->
            <b-form-group
                label="Title:"
                label-for="template-title"
            >
                <b-form-input 
                    id="template-title"
                    v-model="template.title" 
                    placeholder="Enter template title" 
                    type="text"
                ></b-form-input>
            </b-form-group>
            <b-row v-if='errors.title' class='error-message'>
                <b-col cols="12">
                    <p>{{ errors.title[0] }}</p>
                </b-col>
            </b-row>

            <!-- Checkboxes -->
            <div v-for="(banner_type, title) in banner_types" :key="title + 'checkbox'">
                <b-form-checkbox v-model="template.banner_types[title].enabled" >
                {{ title }}
                </b-form-checkbox>
            </div>
            <b-row v-if='errors.banner_types' class='error-message'>
                <b-col cols="12">
                    <p>{{ errors.banner_types[0] }}</p>
                </b-col>
            </b-row>

            <!-- Canvases for Banners -->
            <div v-for="(banner_type, key) in template.banner_types" :key="key" class="pb-3">
                <div v-if="banner_type.enabled && banner_types[key]" class="d-flex">
                    <div :ref="key" class="template-block">
                        <div :class="'position-relative mx-auto preview-' + key"
                            :style="
                            'width: ' + (banner_types[key].width * banner_type.scale) + 'px;' +
                            'height: ' + (banner_types[key].height * banner_type.scale) + 'px;'
                            "
                        >

                            <div v-for="(block, key1) in banner_type.blocks" :key="key1">
                                <div class="position-absolute text-center preview-block" v-if="block.enabled"

                                    :style="
                                    'top:' + (block.top_offset * banner_type.scale) + 'px;' +
                                    'left:' + (block.left_offset * banner_type.scale) + 'px;' +
                                    'width:' + (block.width * banner_type.scale) + 'px;' +
                                    'height:' + (block.height * banner_type.scale) + 'px;' +
                                    'z-index: ' + block.z_index + ';'"

                                    @mousedown="mouseDownBlock($event, key1, key)"
                                >
                                    <span>{{block.block_type.title}}</span>
                                    <div class="drag drag-top drag-left" @mousedown="dragBlock($event, key1, key, 1, 1)"></div>
                                    <div class="drag drag-top drag-right" @mousedown="dragBlock($event, key1, key, 1, 0)"></div>
                                    <div class="drag drag-bottom drag-left" @mousedown="dragBlock($event, key1, key, 0, 1)"></div>
                                    <div class="drag drag-bottom drag-right" @mousedown="dragBlock($event, key1, key, 0, 0)"></div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="position-relative template-sidebar">
                        <div class="position-absolute bg-white"
                            v-for="(block, key1) in banner_type.blocks" :key="key1" @mousedown="mouseDownList($event, key1, key)" 
                            :style="'top:' + ((key1 * 30) + ((block.block_type.title == draggable_element.list) ? list_offset : '')) + 'px; border: 1px solid'">
                            <a @click='toggleBlock(key1, key)'>Click</a>
                            <span>{{ block.block_type.title }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <b-row>
                <b-col cols="12">
                    <b-button type="submit" variant="success">Save</b-button>
                </b-col>
            </b-row>
        </b-form>
    </div>
</template>
<script>
    import axios from 'axios';
    export default {
        data() {
            return {
                template: {
                    id: 0,
                    title: '',
                    banner_types: {
                        giga: {
                            enabled: true,
                            scale: 1,
                            blocks: []
                        },
                        tower: {
                            enabled: false,
                            scale: 1,
                            blocks: []
                        },
                        square: {
                            enabled: false,
                            scale: 1,
                            blocks: []
                        }
                    },
                },
                draggable_element: {
                    key: 0,
                    type: '',
                    x: 0,
                    y: 0,
                    start_x: 0,
                    start_y: 0,
                    list: '',
                    drag: {
                        top: 0,
                        left: 0
                    }
                },
                banner_types: { },
                list_offset: 0,
                errors: {
                    title: '',
                    banner_types: ''
                }
            }
        },
        created() {
            let id = this.$route.params.id;
            
            this.getBannerTypeInfo();
            this.getTemplateInfo();
        },

        mounted() {
            window.addEventListener("resize", this.calculateAllElementScales);
        },

        updated() {
            this.calculateAllElementScales();
        },

        methods: {
            getBannerTypeInfo() {
                axios.get("/banner-types/")
                    .then((response) => {
                        this.banner_types = response.data.banner_types;
                    }).catch((error) => {
                        //ToDo: error
                    });
            },

            getTemplateInfo() {
                let id = 0;
                if(this.$route.params.id > 0) {
                    id = this.$route.params.id;
                }

                axios.get("/banner-blocks/", { params: { id: id } })
                    .then((response) => {
                        Object.keys(this.banner_types).forEach((key) => {
                            if(typeof response.data.blocks[key] != 'undefined') {
                                this.template.banner_types[key].blocks = response.data.blocks[key];
                            }
                        });
                    }).catch((error) => {
                        //ToDo: error
                    });
            },

            async saveTemplate(event) {
                event.preventDefault();
                await axios.post("/user/template", this.template)
                    .then((response) => {
                        //ToDo: success
                    }).catch((error) => {
                        //ToDo: error
                    });
            },

            // Run scaling function for all banner types
            calculateAllElementScales() {
                for( const [key, banner_type]  of Object.entries(this.template.banner_types)) {
                    this.calculateScale(key);
                }
            },
            // Downscale the preview blocks for smaller screens
            calculateScale(block) {
                if(
                    typeof this.$refs[block] !== 'undefined' &&
                    typeof this.$refs[block][0] !== 'undefined'    
                ) {
                    let new_scale = this.$refs[block][0].clientWidth / this.banner_types[block].width;

                    if(new_scale <= 1) {
                        this.template.banner_types[block].scale = new_scale;
                    }
                }
            },

            //Toggle draggable blocks
            toggleBlock(key, type) {
                this.template.banner_types[type].blocks[key].enabled = !this.template.banner_types[type].blocks[key].enabled;
            },


            /* Block moving */
            //Start block dragging
            mouseDownBlock(event, key, type) {
                if(!event.path[0].classList.contains('drag')) {
                    event.preventDefault();

                    let block = this.template.banner_types[type].blocks[key];

                    //Set block starting info
                    this.draggable_element.key = key;
                    this.draggable_element.type = type;
                    this.draggable_element.x = event.clientX;
                    this.draggable_element.y = event.clientY;
                    this.draggable_element.start_x = block.left_offset;
                    this.draggable_element.start_y = block.top_offset;

                    //Add listeners for mouse movements
                    document.onmousemove = this.mouseMoveBlock;
                    document.onmouseup = this.mouseUpBlock;
                }
            },
            //Change block position according to mouse position
            mouseMoveBlock(event) {
                event.preventDefault();

                let scale = this.template.banner_types[this.draggable_element.type].scale;
                let movementX = (this.draggable_element.x - event.clientX) / scale;
                let movementY = (this.draggable_element.y - event.clientY) / scale;

                let block = this.template.banner_types[this.draggable_element.type].blocks[this.draggable_element.key];

                block.left_offset = this.draggable_element.start_x - movementX;
                block.top_offset = this.draggable_element.start_y - movementY;
            },
            mouseUpBlock() {
                let block = this.template.banner_types[this.draggable_element.type].blocks[this.draggable_element.key];

                //Test if out of bounds X axis
                if(block.left_offset < 0) {
                    block.left_offset = 0;
                } else if(block.left_offset + block.width > this.banner_types[this.draggable_element.type].width) {
                    block.left_offset = this.banner_types[this.draggable_element.type].width - block.width;
                }


                //Test if out of bounds Y axis
                if(block.top_offset < 0) { 
                    block.top_offset = 0;
                } else if(block.top_offset + block.height > this.banner_types[this.draggable_element.type].height) {
                    block.top_offset = this.banner_types[this.draggable_element.type].height - block.height;
                }

                this.resetMouseListeners();
            },
            /* End of block moving */

            /* Dragging block */
            dragBlock(event, key, type, top, left) {
                event.preventDefault();

                let block = this.template.banner_types[type].blocks[key];

                this.draggable_element.key = key;
                this.draggable_element.type = type;
                this.draggable_element.x = event.clientX;
                this.draggable_element.y = event.clientY;
                this.draggable_element.drag.top = top;
                this.draggable_element.drag.left = left;

                //Add listeners for mouse movements
                document.onmousemove = this.mouseDragBlock;
                document.onmouseup = this.resetMouseListeners;
            },
            mouseDragBlock() {
                let scale = this.template.banner_types[this.draggable_element.type].scale;
                let movementX = (this.draggable_element.x - event.clientX) / scale;
                let movementY = (this.draggable_element.y - event.clientY) / scale;
                let block = this.template.banner_types[this.draggable_element.type].blocks[this.draggable_element.key];

                if(block.width + movementX > 30){
                    if(this.draggable_element.drag.left == 0) {
                        block.width -= movementX;
                    } else {
                        block.width += movementX;
                        block.left_offset -= movementX;
                    }

                    this.draggable_element.x = event.clientX;
                }

                if(block.height + movementY > 30) {
                    if(this.draggable_element.drag.top == 0) {
                        block.height -= movementY;
                    } else {
                        block.height += movementY;
                        block.top_offset -= movementY;
                    }

                    this.draggable_element.y = event.clientY;
                }
            },
            /* End of dragging block */


            /* List moving */
            mouseDownList(event, key, type) {
                event.preventDefault();

                //Set starting positions
                this.draggable_element.key = key;
                this.draggable_element.type = type;
                this.draggable_element.y = event.clientY;
                this.draggable_element.start_y = key * 30;
                this.draggable_element.list = this.template.banner_types[type].blocks[key].block_type.title;

                //Add new mouse listeners so movement can be stopped
                document.onmousemove = this.mouseMoveList;
                document.onmouseup = this.resetMouseListeners;
            },
            // Increase z-index for moving element
            moveListElementForward(startingKey) {
                let old_element = this.template.banner_types[this.draggable_element.type].blocks[startingKey - 1];
                let new_element = this.template.banner_types[this.draggable_element.type].blocks[startingKey];
                old_element.z_index -= 1;
                new_element.z_index += 1;
                this.template.banner_types[this.draggable_element.type].blocks[startingKey - 1] = new_element;
                this.template.banner_types[this.draggable_element.type].blocks[startingKey] = old_element;
            },
            // Decrease z-index for moving element
            moveListElementBackward(startingKey) {
                let old_element = this.template.banner_types[this.draggable_element.type].blocks[startingKey + 1];
                let new_element = this.template.banner_types[this.draggable_element.type].blocks[startingKey];
                old_element.z_index += 1;
                new_element.z_index -= 1;
                this.template.banner_types[this.draggable_element.type].blocks[startingKey + 1] = new_element;
                this.template.banner_types[this.draggable_element.type].blocks[startingKey] = old_element;
            },
            mouseMoveList() {
                event.preventDefault();

                let movementY = this.draggable_element.y - event.clientY;
                let max_keys = this.template.banner_types[this.draggable_element.type].blocks.length; // Max amount of elements in list

                if(
                    //Make sure that the block doesn't go out of bounds to the bottom side
                    (
                        this.draggable_element.key + 1 == max_keys 
                        && 
                        movementY < 0
                    ) 
                    
                    ||

                    //Make sure that the block doesn't go out of bounds to the top side
                    (
                        this.draggable_element.key == 0 
                        &&
                        movementY > 0
                    )
                ) {
                    movementY = 0;
                }

                this.list_offset = -movementY;
                
                if(
                    movementY < -15 && 
                    this.draggable_element.key - 1 !== max_keys
                ) {
                    // If element needs to be moved to a higher position
                    this.moveListElementBackward(this.draggable_element.key);
                    this.draggable_element.key += 1;
                    this.draggable_element.y += 30;

                    // Reset movementY
                    this.mouseMoveList();
                } else if(
                    movementY > 15 && 
                    this.draggable_element.key !== 0
                ) {
                    // If element needs to be moved to a lower position
                    this.moveListElementForward(this.draggable_element.key);
                    this.draggable_element.key -= 1;
                    this.draggable_element.y -= 30;

                    // Reset movementY
                    this.mouseMoveList();
                }
            },
            /* End of list moving */

            //Reset listeners and coordinates
            resetMouseListeners() {
                document.onmousemove = null;
                document.onmouseup = null;
                this.draggable_element = {
                    key: 0,
                    type: '',
                    x: 0,
                    y: 0,
                    start_x: 0,
                    start_y: 0,
                    list: '',
                    drag: {
                        top: 0,
                        left: 0
                    }
                };
                this.list_offset = 0;
            },
        }
    }

</script>