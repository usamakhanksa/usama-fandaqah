<template>
        <p>
            <!-- <input type="number" class="alter_total new_subtotal_input" v-model="displayValue" @blur="isInputActive = false" @focus="isInputActive = true"/> -->
            <input type="text"
                    class="alter_total new_subtotal_input"
                    @keypress="isNumberKey"
                    v-model="displayValue"
                    @blur="isInputActive = false"
                    @focus="isInputActive = true"
                    :placeholder="__('Price Reservation')"
            />

        </p>
</template>

<script>
    export default {
        name: "SubtotalComponent",
        props : ['value'],
        data(){
            return {
                isInputActive: false
            }
        },
        computed: {
            displayValue: {
                get: function() {
                    if (this.isInputActive) {
                        // Cursor is inside the input field. unformat display value for user
                        return this.value.toString()
                    } else {
                        // User is not modifying now. Format display value for user interface
                        return  parseFloat(this.value).toFixed(2);
                    }

                },
                set: function(modifiedValue) {
                    // Recalculate value after ignoring "$" and "," in user input
                    let newValue = parseFloat(modifiedValue.replace(/[^\d\.]/g, ""))
                    // Ensure that it is not NaN
                    if (isNaN(newValue)) {
                        newValue = 0
                    }
                    // Note: we cannot set this.value as it is a "prop". It needs to be passed to parent component
                    // $emit the event so that parent component gets it
                    Nova.$emit('subtotal', newValue)
                }
            }
        },
        methods: {
            isNumberKey(event)
            {
                let keyCode = (event.keyCode ? event.keyCode : event.which);
                if ((keyCode < 48 || keyCode > 57) && keyCode !== 46) { // 46 is dot
                    event.preventDefault();
                }
            },
        }
    }
</script>

<style scoped>

</style>
