<template>
  <div>
    <select @change="handleChange($event)">
      <option value="" selected>{{ __('Deposit Type') }}</option>
      <option v-for="(term , index) in terms"  :selected="term.id == term_id ? true : false" :value="term.id">{{term.name[local]}}</option>
    </select>
  </div>
</template>

<script>

export default {
    name : 'deposit-transaction-type',
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
                    .get('/nova-vendor/calender/terms/cash-receipt')
                    .then(response => {
                        this.terms = response.data;

                    }).catch(err => {
                    // this.serviceLoading = false;
                    // this.$toasted.show(this.__(err), {type: 'error'})
                })
        // Receive the event bus
        Nova.$on('reset-clicked-deposit',()=>{
            this.term_id = '' ;
        });
    },
    watch:{
        term_id(id){

                if(id == 0){
                    this.term_id = 0 ;
                }
                Nova.$emit('deposit-transaction-type-changed' , this.term_id) ;
            },
    }

}
</script>
