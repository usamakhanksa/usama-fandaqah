<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Sidebar Menu Access</h1>
        <p class="text-slate-500 text-sm">Manage visibility and permissions for sidebar menu items</p>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <div class="space-y-6">
        <div class="flex justify-between items-center">
          <h2 class="text-lg font-bold text-slate-800">Menu Visibility Control</h2>
          <button class="btn-primary px-4 py-2 rounded-lg text-sm font-medium">
            Save Changes
          </button>
        </div>
        
        <div class="border border-slate-200 rounded-xl divide-y divide-slate-100">
          <div class="p-4 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                </svg>
              </div>
              <div>
                <h3 class="font-medium text-slate-800">Dashboard</h3>
                <p class="text-xs text-slate-500">Main dashboard section</p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <div class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-600">Visible</div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" checked>
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
              </label>
            </div>
          </div>
          
          <div class="p-4 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                </svg>
              </div>
              <div>
                <h3 class="font-medium text-slate-800">Finance</h3>
                <p class="text-xs text-slate-500">Financial management section</p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <div class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-600">Visible</div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" checked>
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
              </label>
            </div>
          </div>
          
          <div class="p-4 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-600" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                </svg>
              </div>
              <div>
                <h3 class="font-medium text-slate-800">Settings</h3>
                <p class="text-xs text-slate-500">System settings section</p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <div class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-600">Visible</div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" checked>
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// This is a placeholder component for the sidebar access page
</script>