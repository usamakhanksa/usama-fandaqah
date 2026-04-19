<template>
  <div>
    <select @change="handleChange($event)">
      <option value="" selected>{{ __('Withdraw Type') }}</option>
      <option v-for="(term , index) in terms"  :selected="term.id == term_id ? true : false" :value="term.id">{{term.name[local]}}</option>
    </select>
  </div>
</template>

<script>

export default {
    name : 'withdraw-transaction-type',
    data(){
      return {
          terms:{
              type:Object
          },
          local:Nova.config.local ,
          term_id : 0
      }
    },
    methods: {
        handleChange(event) {
            this.term_id = event.target.value ;
            this.$emit('change')
        },
    },
    created() {

        Nova.request()
                    .get('/nova-vendor/calender/terms/payment-voucher')
                        .then(response => {
                            this.terms = response.data;

                        });

        // Receive the event bus
        Nova.$on('reset-clicked-withdraw',()=>{
           this.term_id = '' ;
        });
    },
    watch:{
        term_id(id){

                if(id == 0){
                    this.term_id = 0 ;
                }

                Nova.$emit('withdraw-transaction-type-changed' , this.term_id) ;
            },
    }

}
</script>