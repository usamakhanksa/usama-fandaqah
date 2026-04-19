<template>


        <div class="flex border-b border-40">
            <div class="w-1/4 py-4">
                <slot>
                    <h4 class="font-normal text-80">{{ field.name }}</h4>
                </slot>
            </div>
            <div class="w-3/4 py-4">
                <slot name="value">
                    <template v-if="field.value">

                        <div class="gallery">
                            <!---->
                            <div class="gallery-list clearfix">
                                <template v-for="image in field.value">
                                    <div class="gallery-item gallery-item-image mb-3 p-3 mr-3">
                                        <div class="gallery-item-info p-3">

                                            <a :href="image" target="_blank" class="preview">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 20 20" aria-labelledby="search" role="presentation" class="fill-current">
                                                    <path fill-rule="nonzero" d="M14.32 12.906l5.387 5.387a1 1 0 0 1-1.414 1.414l-5.387-5.387a8 8 0 1 1 1.414-1.414zM8 14A6 6 0 1 0 8 2a6 6 0 0 0 0 12z"></path>
                                                </svg>
                                            </a>
                                            <!---->
                                        </div>
                                        <img :src="image" alt="ijun524xzebqhdscvszh" class="gallery-image">
                                    </div>
                                </template>

                                <!---->
                            </div>
                            <!---->
                            <!---->
                        </div>
                    </template>
                    <template v-else>
                        <p>&mdash;</p>
                    </template>

                </slot>
            </div>
        </div>

</template>

<script>
export default {
    props: ['resource', 'resourceName', 'resourceId', 'field'],
    mounted() {
        console.log(this.field)
    }
}
</script>
