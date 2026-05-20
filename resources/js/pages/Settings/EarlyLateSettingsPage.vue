<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Early/Late Charges</h1>
        <p class="text-slate-500 text-sm">Configure charges for early check-in and late check-out</p>
      </div>
      <button class="btn-primary px-4 py-2 rounded-lg text-sm font-medium">
        Save Settings
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Early Check-In Charges</h2>
        
        <div class="space-y-4">
          <div class="flex items-center">
            <label class="inline-flex items-center">
              <input type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
              <span class="ml-2 text-sm text-slate-700">Enable early check-in charges</span>
            </label>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Default Check-In Time</label>
            <input 
              type="time" 
              class="input w-full" 
              value="15:00"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Charge Calculation Method</label>
            <select class="input w-full">
              <option>Fixed Amount per Hour</option>
              <option>Percentage of Nightly Rate</option>
              <option>Fixed Amount per Occurrence</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Charge Rate</label>
            <div class="flex gap-2">
              <input 
                type="number" 
                class="input flex-1" 
                value="50"
                min="0"
              />
              <select class="input w-32">
                <option>SAR</option>
                <option>%</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Late Check-Out Charges</h2>
        
        <div class="space-y-4">
          <div class="flex items-center">
            <label class="inline-flex items-center">
              <input type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
              <span class="ml-2 text-sm text-slate-700">Enable late check-out charges</span>
            </label>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Default Check-Out Time</label>
            <input 
              type="time" 
              class="input w-full" 
              value="12:00"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Charge Calculation Method</label>
            <select class="input w-full">
              <option>Half Day Rate</option>
              <option>Full Day Rate</option>
              <option>Hourly Rate</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Charge Rate</label>
            <div class="flex gap-2">
              <input 
                type="number" 
                class="input flex-1" 
                value="75"
                min="0"
              />
              <select class="input w-32">
                <option>SAR</option>
                <option>%</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="text-lg font-bold text-slate-800 mb-4">Advanced Configuration</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h3 class="font-medium text-slate-800 mb-3">Early Check-In Tiers</h3>
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <div>
                <div class="text-sm font-medium">Before 6:00 AM</div>
                <div class="text-xs text-slate-500">Full night charge</div>
              </div>
              <span class="text-sm font-bold">100% Rate</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <div>
                <div class="text-sm font-medium">6:00 AM - 9:00 AM</div>
                <div class="text-xs text-slate-500">Half night charge</div>
              </div>
              <span class="text-sm font-bold">50% Rate</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <div>
                <div class="text-sm font-medium">9:00 AM - 12:00 PM</div>
                <div class="text-xs text-slate-500">Small fee</div>
              </div>
              <span class="text-sm font-bold">25% Rate</span>
            </div>
          </div>
        </div>
        
        <div>
          <h3 class="font-medium text-slate-800 mb-3">Late Check-Out Tiers</h3>
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <div>
                <div class="text-sm font-medium">Until 2:00 PM</div>
                <div class="text-xs text-slate-500">Small extension fee</div>
              </div>
              <span class="text-sm font-bold">25% Rate</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <div>
                <div class="text-sm font-medium">Until 6:00 PM</div>
                <div class="text-xs text-slate-500">Half day charge</div>
              </div>
              <span class="text-sm font-bold">50% Rate</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <div>
                <div class="text-sm font-medium">After 6:00 PM</div>
                <div class="text-xs text-slate-500">Full day charge</div>
              </div>
              <span class="text-sm font-bold">100% Rate</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="text-lg font-bold text-slate-800 mb-4">Exemptions</h2>
      
      <div class="space-y-3">
        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
          <div>
            <div class="text-sm font-medium">VIP Customers</div>
            <div class="text-xs text-slate-500">Exempt from early/late charges</div>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" class="sr-only peer" checked>
            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
          </label>
        </div>
        
        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
          <div>
            <div class="text-sm font-medium">Long-term Stays</div>
            <div class="text-xs text-slate-500">Over 7 nights exempt</div>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" class="sr-only peer">
            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
          </label>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// This is a placeholder component for the early/late settings page
</script>