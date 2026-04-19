<template>
  <div class="p-4">
    <div class="card p-6 text-center space-y-4 max-w-3xl mx-auto">
      <ReservationStepper :current="steps.length" :steps="steps" />
      <img :src="'/assets/banners/banner2.svg'" class="h-52 mx-auto" alt="success" />
      <h1 class="text-4xl font-bold">{{ data.title }}</h1>
      <p class="text-slate-500">{{ data.subtitle }}</p>
      <div class="flex items-center justify-center gap-3">
        <button class="btn-primary" @click="router.push('/dashboard')">Back To Home</button>
        <a class="btn-outline" :href="data.receipt_url" target="_blank">Print Receipt</a>
      </div>
    </div>
  </div>
</template>
<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../services/api';
import ReservationStepper from '../components/ReservationStepper.vue';

const props = defineProps({
  flow: { type: String, default: 'reservation' },
});
const route = useRoute();
const router = useRouter();
const data = ref({ title: 'Room Has Been Successfully Booked', subtitle: 'Be Ready To Check IN', receipt_url: '#' });
const steps = computed(() => props.flow === 'checkout'
  ? ['Insurance Recovery', 'Payment of indebtedness', 'Check-Out']
  : ['Reservation Details', 'Visitor Information', 'Payment & Confirmation']);

onMounted(async () => {
  if (props.flow === 'checkout') {
    data.value = {
      title: 'Check Out Has Been Successfull',
      subtitle: `You Have Successfully Checked Out From The Room Number (${route.params.bookingId})`,
      receipt_url: '#',
    };
    return;
  }
  const res = await api.get(`/reservations/success/${route.params.bookingId}`);
  data.value = res.data;
});
</script>
