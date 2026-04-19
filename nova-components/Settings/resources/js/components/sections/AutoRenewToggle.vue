<template>
    <default-field :field="field">
        <template slot="field">
            <div class="py-2">
                <toggle-button
                        :id="sanitize"
                        :name="sanitizedName"
                        :value="value"
                        @change="toggle"
                        :labels="labelConfig"
                        :width="width"
                        :height="height"
                        :sync="true"
                        :color="colors"
                        :speed="speed"
                        ref="smsToggle"

                />

            </div>

            <p v-if="disabled" class="help-text text-red-custom">
                {{__('Please make sure to integrate with at least one sms provider to enable this option')}}
            </p>

        </template>
    </default-field>
</template>

<script>
    import { Errors, FormField, HandlesValidationErrors } from 'laravel-nova'

    export default {
        mixins: [HandlesValidationErrors, FormField],
        name : 'auto-renew-toggle',
        props : ['auto_renew_option'], 
        data: () => ({
            value: false,
        }),

        mounted() {
            
            this.value = this.auto_renew_option;
            // this.value = this.field.value || false
            Nova.$emit('auto-renew-default-value' , this.value);
            this.field.fill = formData => {
                formData.append(this.field.attribute, this.trueValue)
            }

        },

        methods: {
            toggle() {
                this.value = !this.value
                Nova.$emit('auto-renew-option-changed' , this.value); 
            },

        },

        computed: {
                trueValue() {
                    return +Boolean(this.value)
                },

                trueLabel(){
                    return (this.field.true_label != undefined) ? this.field.true_label : 'True'
                },

                falseLabel(){
                    return (this.field.false_label != undefined) ? this.field.false_label : 'False'
                },

                labelConfig(){
                    return {
                        checked:  (this.field.show_true_label) ? this.trueLabel : null,
                        unchecked: (this.field.show_false_label) ? this.falseLabel : null,
                    }
                },

                width(){
                    return (this.field.width != undefined) ? this.field.width : 60 //50
                },

                height(){
                    return (this.field.height != undefined) ? this.field.height : 26 //22
                },

                trueColor(){
                    return (this.field.true_color != undefined) ? this.field.true_color : 'var(--success)'
                },

                falseColor(){
                    return (this.field.false_color != undefined) ? this.field.false_color : 'var(--60)'
                },

                colors(){
                    return {
                        checked: this.trueColor,
                        unchecked: this.falseColor,
                        disabled: '#CCCCCC'
                    }
                },

                speed(){
                    return (this.field.speed != undefined) ? this.field.speed : 300
                },

                sanitize() {
                return this.field.name.normalize('NFD').replace(/[\u0300-\u036f]/g, "")
            },
        },
    }
</script>

<style scoped>
    .text-red-custom{
        color: red;
    }
</style>
