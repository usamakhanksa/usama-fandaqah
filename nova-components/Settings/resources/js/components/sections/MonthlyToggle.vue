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
                        :disabled="disabled"
                        :aria-checked="disabled"
                        

                />
                 <p v-if="showWarning" class="help-text text-red-custom">
                    {{__('Enable Auto Renew Reservations Option')}}
                </p>
            </div>
        </template>
    </default-field>
</template>

<script>
    import { Errors, FormField, HandlesValidationErrors } from 'laravel-nova'

    export default {
        mixins: [HandlesValidationErrors, FormField],
        name : 'monthly-toggle',
        data: () => ({
            value: false,
            auto_renew_toggle_value : false,
            showWarning : false,
            disabled : false
        }),

        mounted() {
            this.getSettings();
            Nova.$on('auto-renew-option-changed' , (val) => {
                this.auto_renew_toggle_value = val;
                if(!val){
                     this.value =  false;
                     this.showWarning = true;
                     this.disabled = true;
                     return ;
                }else{
                    this.showWarning = false;
                    this.disabled = false;
                }
            })
            this.value = this.field.value || false;
            this.field.fill = formData => {
                formData.append(this.field.attribute, this.trueValue)
            }

        },

        methods: {
            getSettings() {
                const self = this;
                Nova.request()
                    .get('/nova-vendor/settings/get/' + 'general').then(response => {
                    _.forEach(response.data.items , function(item){
                        if(item.key == 'automatic_renewal_of_reservations'){
                            self.auto_renew_toggle_value = item.value;
                            if(!self.auto_renew_toggle_value){
                                self.showWarning = true;
                                self.disabled = true;
                                self.value = false;
                                return ;
                            }else{
                                self.disabled = false
                                self.showWarning = false;
                                self.value = !self.value
                                self.value = self.field.value || false;
                                self.field.fill = formData => {
                                    formData.append(self.field.attribute, self.trueValue)
                                }
                            }
                        }
                    })
                })
                .catch(error => {
                    this.$toasted.error(this.translations['load_error'], {
                        duration: 3000
                    });
                });
            },
            toggle() {

                if(!this.auto_renew_toggle_value){
                    this.showWarning = true;
                    this.disabled = true;
                    this.value = false;
                    return ;
                }else{
                    this.disabled = false
                    this.showWarning = false;
                    this.value = !this.value
                }
               
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
