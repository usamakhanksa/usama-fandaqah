<template>
	<div>
 	</div>
</template>

<script>
    export default {
        props: {
            textAlign : {
                type : String ,
                default : "text-left"
            }
        },
        data: () => {
            return {
                loading: false,
            }
        },
        mounted() { 
            this.getUnits();
            let lang = Nova.config.local ;
            lang == 'en' ? this.textAlign = 'text-left' : this.textAlign = 'text-right' ;
        },
        methods: {
        },
    }
</script>

<style>
</style>