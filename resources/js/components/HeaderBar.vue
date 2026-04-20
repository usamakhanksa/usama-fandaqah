<template>
  <header class="bg-white border-b px-4 py-2 flex flex-col gap-0">
    <!-- Top Row -->
    <div class="flex items-center justify-between gap-3">
      <!-- Left: menu + logo -->
      <div class="flex items-center gap-3">
        <button class="p-2 rounded hover:bg-slate-100 text-slate-600" aria-label="Toggle sidebar">☰</button>
        <!-- Logo -->
        <router-link to="/dashboard" class="flex items-center gap-2 no-underline">
          <img v-if="slider.logoUrl" :src="slider.logoUrl" alt="Logo" class="h-8 w-auto object-contain" />
          <div v-else class="w-8 h-8 rounded-full bg-rose-100 border border-rose-300 flex items-center justify-center text-rose-600 font-black text-xs">
            {{ slider.logoText.charAt(0).toUpperCase() }}
          </div>
          <span class="font-bold text-base text-slate-800 tracking-tight">{{ slider.logoText }}</span>
        </router-link>
        <div class="w-px h-6 bg-slate-200 mx-1 hidden sm:block" />
        <router-link to="/reservations/create" class="btn-primary text-xs hidden sm:inline-flex">+ New Booking</router-link>
      </div>

      <!-- Center: search -->
      <SearchInput class="max-w-sm flex-1 hidden md:block" />

      <!-- Right: notifications + user -->
      <div class="flex items-center gap-2">
        <NotificationDropdown />
        <UserMenu />
      </div>
    </div>

    <!-- Feature Slider -->
    <FeatureSlider />
  </header>
</template>

<script setup>
import SearchInput from './SearchInput.vue';
import NotificationDropdown from './NotificationDropdown.vue';
import UserMenu from './UserMenu.vue';
import FeatureSlider from './FeatureSlider.vue';
import { useSliderStore } from '../stores/slider';

const slider = useSliderStore();
</script>
