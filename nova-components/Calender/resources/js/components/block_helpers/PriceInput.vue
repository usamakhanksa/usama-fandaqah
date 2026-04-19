<template>
    <div>
        <input class="price_input_input" type="number" :disabled="disabled" :key="key" :value="value" @input="onChangeInput"/>
    </div>
</template>


<script>
export default {
    name: 'price-input',
    props: ['value', 'identifier', 'disabled'],
    data: () => {
        return {
            mutatedValue: {
                value: null,
                identifier: null,
            }
        }
    },
    computed: {
        mutatedValueInput: {
            set(value) {
                this.mutatedValue = {
                    value,
                    identifier: this.identifier
                }
            }
        }
    },
    methods: {
        onChangeInput (event) {
            this.mutatedValueInput = event.target.value;
            this.$emit('updatePrice', this.mutatedValue); 
        }
    }
}
</script>
<style scoped>
 .price_input_input {
    text-align: end;
    background: transparent;
    max-width: 100px;
    border: 1px solid #dddddd;
    padding: 7px;
 }
</style>