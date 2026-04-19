<template>
    <div>
        <OnlineReservation v-for="reservation in reservations" :key="reservation.id" :reservation="reservation" v-if="reservation.status === 'waiting'"></OnlineReservation>
    </div>
</template>

<script>
    import OnlineReservation from './partial/OnlineReservation';

    export default {
        name: "OnlineReservations",
        props: {
            reservations: {
                type: Array,
                default: []
            }
        },
        components: {
            OnlineReservation
        },
        mounted() {
        }
    }
</script>

<style scoped>

</style>
