<template>
  <div class="p-4 space-y-4">
    <div class="card p-4"><ReservationStepper :current="step" :steps="['Reservation Details', 'Visitor Information', 'Payment & Confirmation']" /></div>

    <div v-if="step===1" class="card p-4 space-y-4">
      <div class="grid md:grid-cols-3 gap-3">
        <div><label class="label">Booking Type</label><div class="flex gap-4 text-sm"><label><input type="radio" v-model="details.booking_type" value="single"> Single</label><label><input type="radio" v-model="details.booking_type" value="group"> Group</label></div></div>
        <div><label class="label">Check-In</label><input v-model="details.check_in" type="date" class="input"></div>
        <div><label class="label">Check-Out</label><input v-model="details.check_out" type="date" class="input"></div>
      </div>
      <div class="grid md:grid-cols-3 gap-3">
        <div><label class="label">Unit Type</label><input v-model="details.unit_type" class="input"></div>
        <div><label class="label">Unit(Optional)</label><input v-model="details.unit" class="input"></div>
        <div><label class="label">Choose Room Number(Optional)</label><select v-model="details.room_id" class="input"><option v-for="room in rooms" :value="room.id" :key="room.id">{{ room.number }} - {{ room.name }}</option></select></div>
      </div>
    </div>

    <div v-if="step===2" class="card p-4 space-y-4">
      <input v-model="visitor.search" class="input" placeholder="Search By ID number / Email / Name / Mobile number">
      <div class="grid md:grid-cols-3 gap-3">
        <div><label class="label">Visitor Name</label><input v-model="visitor.visitor_name" class="input"></div>
        <div><label class="label">Visitor Type</label><input v-model="visitor.visitor_type" class="input"></div>
        <div><label class="label">Nationality</label><input v-model="visitor.nationality" class="input"></div>
        <div><label class="label">Phone</label><input v-model="visitor.phone" class="input"></div>
        <div><label class="label">ID Type</label><input v-model="visitor.id_type" class="input"></div>
        <div><label class="label">ID Number</label><input v-model="visitor.id_number" class="input"></div>
        <div><label class="label">Email</label><input v-model="visitor.email" class="input"></div>
        <div><label class="label">Birthday</label><input v-model="visitor.birthday" type="date" class="input"></div>
        <div><label class="label">Gender</label><div class="flex gap-4 text-sm pt-3"><label><input type="radio" v-model="visitor.gender" value="male"> Male</label><label><input type="radio" v-model="visitor.gender" value="female"> Female</label></div></div>
      </div>
    </div>

    <div v-if="step===3" class="grid lg:grid-cols-[1fr,260px] gap-4">
      <div class="card p-4 space-y-4">
        <div class="grid md:grid-cols-[1fr,140px] gap-2">
          <input v-model="payment.promo_code" class="input" placeholder="Please add your promo here">
          <button class="btn-primary" @click="applyPromo">Apply Promo</button>
        </div>
        <div class="grid md:grid-cols-3 gap-2 text-sm">
          <button class="card p-3" :class="payment.payment_method==='cash'?'ring-2 ring-red-400':''" @click="payment.payment_method='cash'">Cash</button>
          <button class="card p-3" :class="payment.payment_method==='pos'?'ring-2 ring-red-400':''" @click="payment.payment_method='pos'">POS</button>
          <button class="card p-3" :class="payment.payment_method==='on_arrival'?'ring-2 ring-red-400':''" @click="payment.payment_method='on_arrival'">On Arrival</button>
        </div>
      </div>
      <div class="card p-4 text-sm space-y-2">
        <div class="flex justify-between"><span>{{ roomPrice }} SAR / night</span><span>4.91 (98)</span></div>
        <div class="flex justify-between"><span>Subtotal</span><span>{{ subtotal.toFixed(2) }} SAR</span></div>
        <div class="flex justify-between"><span>Discount</span><span>{{ (payment.discount || 0).toFixed(2) }} SAR</span></div>
        <div class="flex justify-between"><span>Cleaning fee</span><span>25.50 SAR</span></div>
        <div class="flex justify-between"><span>Service fee</span><span>35.00 SAR</span></div>
        <div class="flex justify-between font-bold text-lg"><span>Total</span><span>{{ total.toFixed(2) }} SAR</span></div>
      </div>
    </div>

    <div class="card p-3 flex justify-between">
      <button class="btn-outline" @click="saveDraft">Save draft</button>
      <div class="flex gap-2">
        <button class="btn-outline" @click="prevStep" :disabled="step===1">Back</button>
        <button v-if="step<3" class="btn-primary" @click="nextStep">Next</button>
        <button v-else class="btn-primary" @click="confirm">Confirm</button>
      </div>
    </div>

    <FooterBar />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import ReservationStepper from '../components/ReservationStepper.vue';
import FooterBar from '../components/FooterBar.vue';

const router = useRouter();
const step = ref(1);
const reference = ref('');
const rooms = ref([]);

const details = ref({ booking_type: 'single', check_in: new Date().toISOString().slice(0,10), check_out: new Date(Date.now()+86400000).toISOString().slice(0,10), unit_type: 'Single', unit: 'A-205', room_id: null });
const visitor = ref({ search: '', visitor_name: 'Ahmed Muhamed', visitor_type: 'Citizen', nationality: 'Saudi Arabia', phone: '+966 123456789', id_type: 'ID', id_number: '25352513543254235', email: 'ahmed@example.com', birthday: '', gender: 'male' });
const payment = ref({ promo_code: '', payment_method: 'cash', discount: 0 });

const nights = computed(() => Math.max(1, Math.ceil((new Date(details.value.check_out)-new Date(details.value.check_in))/86400000)));
const roomPrice = computed(() => {
  const room = rooms.value.find((r) => r.id === details.value.room_id);
  return Number(room?.price_per_day || 35);
});
const subtotal = computed(() => roomPrice.value * nights.value);
const total = computed(() => subtotal.value - Number(payment.value.discount || 0) + 25.5 + 35);

const loadRooms = async () => {
  const { data } = await api.get('/rooms', { params: { per_page: 100 } });
  rooms.value = data.data || [];
  if (!details.value.room_id && rooms.value.length) details.value.room_id = rooms.value[0].id;
};

const saveDraft = async () => {
  const { data } = await api.post('/reservations/drafts', {
    reference: reference.value || undefined,
    current_step: step.value,
    details_payload: details.value,
    visitor_payload: visitor.value,
    payment_payload: payment.value,
  });
  reference.value = data.reference;
};

const applyPromo = async () => {
  if (!payment.value.promo_code) return;
  const { data } = await api.post('/reservations/promo/apply', { code: payment.value.promo_code, subtotal: subtotal.value });
  payment.value.discount = data.discount;
};

const nextStep = async () => { await saveDraft(); step.value += 1; };
const prevStep = () => { step.value = Math.max(1, step.value - 1); };

const confirm = async () => {
  const { data } = await api.post('/reservations/confirm', { reference: reference.value || undefined, details: details.value, visitor: visitor.value, payment: payment.value });
  router.push(`/reservations/success/${data.id}`);
};

onMounted(loadRooms);
</script>
