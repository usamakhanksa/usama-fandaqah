<template>
  <main class="bg-slate-100 min-h-[calc(100vh-64px)] p-4 space-y-4">
    <div class="bg-white rounded-xl border p-4"><h1 class="font-bold">POS <span class="text-slate-400 text-sm">Home > POS > Transactions</span></h1></div>
    <div class="bg-white rounded-xl border overflow-hidden"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-3 text-left">#</th><th class="p-3 text-left">Transaction</th><th class="p-3 text-left">Customer</th><th class="p-3 text-left">Cashier</th><th class="p-3 text-left">Payment</th><th class="p-3 text-left">Total</th><th class="p-3 text-left">Status</th></tr></thead><tbody><tr class="border-t" v-for="t in transactions.data" :key="t.id"><td class="p-3">{{ t.id }}</td><td class="p-3">{{ t.transaction_number }}</td><td class="p-3">{{ t.customer_name }}</td><td class="p-3">{{ t.cashier_name }}</td><td class="p-3">{{ t.payment_method }}</td><td class="p-3">{{ t.total }} SAR</td><td class="p-3">{{ t.status }}</td></tr></tbody></table></div>
  </main>
</template>
<script setup>
import { onMounted, ref } from 'vue';
import api from '../services/api';
const transactions = ref({ data: [] });
onMounted(async () => { transactions.value = (await api.get('/pos/transactions')).data; });
</script>
