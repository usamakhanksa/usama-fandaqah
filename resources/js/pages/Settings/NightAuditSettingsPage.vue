<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Night Audit Settings</h1>
        <p class="text-slate-500 text-sm">Configure night audit parameters and automation settings</p>
      </div>
      <button class="btn-primary px-4 py-2 rounded-lg text-sm font-medium">
        Save Settings
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Night Audit Timing</h2>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Auto-run Time</label>
            <input 
              type="time" 
              class="input w-full" 
              value="04:00"
              placeholder="Select time for automatic night audit"
            />
            <p class="text-xs text-slate-500 mt-1">Time when night audit runs automatically (if enabled)</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Night Audit Cut-off Time</label>
            <input 
              type="time" 
              class="input w-full" 
              value="23:59"
              placeholder="Cut-off time for business day"
            />
            <p class="text-xs text-slate-500 mt-1">Time that marks the end of the business day</p>
          </div>
          
          <div class="flex items-center pt-2">
            <label class="inline-flex items-center">
              <input type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
              <span class="ml-2 text-sm text-slate-700">Enable auto-run night audit</span>
            </label>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Business Day Configuration</h2>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Day Start Time</label>
            <input 
              type="time" 
              class="input w-full" 
              value="06:00"
              placeholder="Business day start time"
            />
            <p class="text-xs text-slate-500 mt-1">Time that marks the beginning of the business day</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Default Check-out Time</label>
            <input 
              type="time" 
              class="input w-full" 
              value="12:00"
              placeholder="Default check-out time for reservations"
            />
            <p class="text-xs text-slate-500 mt-1">Standard check-out time for reservations</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Default Check-in Time</label>
            <input 
              type="time" 
              class="input w-full" 
              value="15:00"
              placeholder="Default check-in time for reservations"
            />
            <p class="text-xs text-slate-500 mt-1">Standard check-in time for reservations</p>
          </div>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="text-lg font-bold text-slate-800 mb-4">Audit Rules</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h3 class="font-medium text-slate-800 mb-3">No-Show Rules</h3>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm text-slate-700">Charge for no-show after 2 hours past due</span>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" checked>
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
              </label>
            </div>
            
            <div class="flex items-center justify-between">
              <span class="text-sm text-slate-700">Automatically mark as no-show</span>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
              </label>
            </div>
          </div>
        </div>
        
        <div>
          <h3 class="font-medium text-slate-800 mb-3">Financial Rules</h3>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm text-slate-700">Close financial transactions after night audit</span>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" checked>
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
              </label>
            </div>
            
            <div class="flex items-center justify-between">
              <span class="text-sm text-slate-700">Generate reports automatically</span>
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
// This is a placeholder component for the night audit settings page
</script>