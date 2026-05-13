<template>
  <div class="space-y-4 p-4 bg-gray-50 rounded-lg">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
        <input
          :value="item.description"
          @input="updateField('description', $event.target.value)"
          type="text"
          class="w-full rounded-md border-gray-300"
          placeholder="Item description"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Item Type</label>
        <select
          :value="item.item_type"
          @change="updateField('item_type', $event.target.value)"
          class="w-full rounded-md border-gray-300"
        >
          <option v-for="type in itemTypes" :key="type.value" :value="type.value">
            {{ type.label }}
          </option>
        </select>
      </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
        <input
          :value="item.quantity"
          @input="updateField('quantity', parseFloat($event.target.value) || 0)"
          type="number"
          step="0.01"
          min="0.01"
          class="w-full rounded-md border-gray-300"
          placeholder="1.00"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Unit Price *</label>
        <input
          :value="item.unit_price"
          @input="updateField('unit_price', parseFloat($event.target.value) || 0)"
          type="number"
          step="0.01"
          min="0.01"
          class="w-full rounded-md border-gray-300"
          placeholder="0.00"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Discount Amount</label>
        <input
          :value="item.discount_amount"
          @input="updateField('discount_amount', parseFloat($event.target.value) || 0)"
          type="number"
          step="0.01"
          min="0"
          class="w-full rounded-md border-gray-300"
          placeholder="0.00"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Tax Rate (%)</label>
        <select
          :value="item.tax_rate"
          @change="updateField('tax_rate', parseFloat($event.target.value) || 0)"
          class="w-full rounded-md border-gray-300"
        >
          <option v-for="rate in taxRates" :key="rate.value" :value="rate.value">
            {{ rate.label }}
          </option>
        </select>
      </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Total Price</label>
        <div class="w-full px-3 py-2 bg-gray-100 rounded-md border">
          {{ formatCurrency(calculateTotal()) }}
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Tax Amount</label>
        <div class="w-full px-3 py-2 bg-gray-100 rounded-md border">
          {{ formatCurrency(calculateTax()) }}
        </div>
      </div>
    </div>
    
    <div class="flex justify-between items-center pt-4 border-t">
      <div class="text-sm text-gray-600">
        Net: {{ formatCurrency(calculateNet()) }}
      </div>
      
      <button
        @click="$emit('remove')"
        class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700"
      >
        Remove Item
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  itemTypes: {
    type: Array,
    required: true,
  },
  taxRates: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['update', 'remove']);

const calculateTotal = () => {
  const basePrice = props.item.quantity * props.item.unit_price;
  return basePrice - (props.item.discount_amount || 0);
};

const calculateTax = () => {
  const total = calculateTotal();
  return total * (props.item.tax_rate / 100);
};

const calculateNet = () => {
  return calculateTotal() - calculateTax();
};

const formatCurrency = (amount) => {
  return `﷼ ${Number(amount).toFixed(2)}`;
};

const updateField = (field, value) => {
  emit('update', { field, value });
};
</script>
