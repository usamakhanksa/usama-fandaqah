<template>
<div class="p-6 space-y-6">
  <div class="card p-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold">Channel Manager</h1>
    <p class="text-sm text-slate-400">Home > Channel Manager</p>
  </div>
  
  <div class="grid md:grid-cols-2 gap-6">
    <!-- Availability & Rates -->
    <div class="card overflow-hidden group">
      <div class="h-48 bg-slate-50 relative flex items-center justify-center border-b group-hover:bg-rose-50 transition-colors">
        <span class="text-6xl group-hover:scale-110 transition-transform">📉</span>
      </div>
      <div class="p-6 space-y-4">
        <h2 class="text-xl font-bold">Avaliblity & Rates</h2>
        <p class="text-slate-500 text-sm">Manage category prices, virtual rooms, and rate plan formulas across all channels.</p>
        <router-link to="/channel-manager/availability-rates" class="btn-primary block text-center">Manage Categories</router-link>
      </div>
    </div>

    <!-- Reservations -->
    <div class="card overflow-hidden group">
      <div class="h-48 bg-slate-50 relative flex items-center justify-center border-b group-hover:bg-rose-50 transition-colors">
        <span class="text-6xl group-hover:scale-110 transition-transform">🛎️</span>
      </div>
      <div class="p-6 space-y-4">
        <h2 class="text-xl font-bold">Reservations</h2>
        <p class="text-slate-500 text-sm">Monitor incoming channel bookings, track revenue, and assign units to channel reservations.</p>
        <router-link to="/channel-manager/reservations" class="btn-primary block text-center">View Reservations</router-link>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
</script>
