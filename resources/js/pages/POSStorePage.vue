<template>
  <main class="bg-slate-100 min-h-[calc(100vh-64px)] p-4 space-y-4">
    <div class="bg-white rounded-xl border p-4"><h1 class="font-bold">POS <span class="text-slate-400 text-sm">Home > POS > Store</span></h1></div>
    <div class="grid lg:grid-cols-4 gap-4">
      <div class="lg:col-span-3 space-y-3">
        <div class="grid md:grid-cols-3 gap-3"><POSStoreCard v-for="s in stores" :key="s.id" :store="s" :active="activeStore===s.id" @select="selectStore"/></div>
        <div class="bg-white rounded-xl border p-3 flex gap-2"><input v-model="search" class="flex-1 border rounded px-3 py-2 text-xs" placeholder="Search Product Code / Name ..."/><button class="bg-rose-500 text-white px-4 rounded text-xs">Scan Product</button></div>
        <div v-if="!selectedCategory" class="grid sm:grid-cols-2 md:grid-cols-4 gap-3"><POSCategoryTile v-for="c in categories" :key="c.id" :category="c" @select="selectedCategory=c"/></div>
        <div v-else class="space-y-3">
          <POSCategoryTabs :tabs="[{id:null,name:'All'},...subCategories]" :active-id="activeSubCategory" @select="selectSubCategory"/>
          <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-3"><POSProductCard v-for="p in products.data||[]" :key="p.id" :product="p" :qty="qtyFor(p.id)" @add="addToCart" @quantity="setQty"/></div>
        </div>
      </div>
      <POSCartSidebar :cart="cart" @customer="updateCustomer" @remove="removeFromCart" @quantity="setQtyFromCart" @clear="clearCart" @checkout="checkout"/>
    </div>
  </main>
</template>
<script setup>
import { onMounted, ref, watch } from 'vue';
import api from '../services/api';
import POSStoreCard from '../components/POSStoreCard.vue';
import POSCategoryTile from '../components/POSCategoryTile.vue';
import POSCategoryTabs from '../components/POSCategoryTabs.vue';
import POSProductCard from '../components/POSProductCard.vue';
import POSCartSidebar from '../components/POSCartSidebar.vue';

const stores = ref([]);const categories = ref([]);const subCategories = ref([]);const products = ref({ data: [] });const cart = ref({ items: [], subtotal: 0, tax: 0, discount: 0, total: 0, customer_name: '' });
const activeStore = ref(null);const selectedCategory = ref(null);const activeSubCategory = ref(null);const search = ref('');

const loadStores = async () => { stores.value = (await api.get('/pos/stores')).data; if (!activeStore.value && stores.value.length) { activeStore.value = stores.value[0].id; } };
const loadCategories = async () => { categories.value = (await api.get('/pos/categories', { params: { channel_id: activeStore.value } })).data; };
const loadSubCategories = async () => { if (!selectedCategory.value) return; subCategories.value = (await api.get('/pos/sub-categories', { params: { category_id: selectedCategory.value.id } })).data; };
const loadProducts = async () => { products.value = (await api.get('/pos/products', { params: { channel_id: activeStore.value, category_id: selectedCategory.value?.id, sub_category_id: activeSubCategory.value, search: search.value } })).data; };
const loadCart = async () => { cart.value = (await api.get('/pos/cart')).data; };
const qtyFor = (id) => cart.value.items.find(i => i.id === id)?.quantity || 0;
const saveQty = async (productId, quantity) => { cart.value = (await api.post('/pos/cart/items', { product_id: productId, quantity, store_id: activeStore.value, customer_name: cart.value.customer_name })).data; };
const addToCart = (p) => saveQty(p.id, 1);
const setQty = (p, quantity) => saveQty(p.id, Math.max(0, quantity));
const setQtyFromCart = (item, quantity) => saveQty(item.id, Math.max(0, quantity));
const removeFromCart = (item) => saveQty(item.id, 0);
const clearCart = async () => { cart.value = (await api.delete('/pos/cart/items')).data; };
const checkout = async () => { await api.post('/pos/checkout', { payment_method: 'cash' }); await loadCart(); alert('Order paid successfully'); };
const updateCustomer = async (name) => { cart.value = (await api.post('/pos/cart/items', { quantity: 0, customer_name: name, store_id: activeStore.value })).data; };

const selectStore = async (s) => { activeStore.value = s.id; selectedCategory.value = null; activeSubCategory.value = null; await loadCategories(); await loadProducts(); };
const selectSubCategory = async (sub) => { activeSubCategory.value = sub.id; await loadProducts(); };

watch(search, loadProducts);
watch(selectedCategory, async () => { activeSubCategory.value = null; await loadSubCategories(); await loadProducts(); });

onMounted(async () => { await loadStores(); await loadCategories(); await loadProducts(); await loadCart(); });
</script>
