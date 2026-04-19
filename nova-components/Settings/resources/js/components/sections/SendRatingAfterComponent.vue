<template>
    <default-field :field="field">
        <template slot="field">
            <div class="py-2">
                <input type="number" min="0"  @input="setValue" class="w-full form-control form-input form-input-bordered" v-model="value">
            </div>
        </template>
    </default-field>
</template>

<script>
    import { Errors, FormField, HandlesValidationErrors } from 'laravel-nova'
    export default {
        name: "SendRatingAfterComponent",
        mixins: [HandlesValidationErrors, FormField],
        props : ['settings'],
        data(){
            return {
                value : 0,
                groupName : 'ratings',

            }
        },
        methods:{
            setValue(){
                this.field.value = this.value;
                this.field.fill = formData => {
                    formData.append(this.field.attribute, this.value)
                }
            }
        },
        mounted(){
            this.value = this.field.value || 0
        }
    }
</script>

<style scoped>

</style>
