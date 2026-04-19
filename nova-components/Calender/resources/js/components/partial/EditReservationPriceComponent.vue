<template>
    <p>
        <input type="number" class="total" :disabled="hasInvoices || unitHasChanged" :readonly="price_field_is_read_only"  v-model="displayValue" @blur="isInputActive = false" @focus="isInputActive = true" />
    </p>
</template>

<script>
    export default {
        name: "EditReservationPriceComponent",
        props : ['value' , 'has_invoices' , 'unit_has_changed' , 'price_field_is_read_only' ],
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
                    Nova.$emit('edit_reservation_price', newValue)
                }
            }
        }
    }
</script>

<style scoped>

</style>
